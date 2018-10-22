<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   core_customfield
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\subsystem\plugin_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_database_table(
            'customfield_data',
            [
                'fieldid' => 'privacy:metadata:customfield_data:fieldid',
                'instanceid' => 'privacy:metadata:customfield_data:instanceid',
                'intvalue' => 'privacy:metadata:customfield_data:intvalue',
                'decvalue' => 'privacy:metadata:customfield_data:decvalue',
                'shortcharvalue' => 'privacy:metadata:customfield_data:shortcharvalue',
                'charvalue' => 'privacy:metadata:customfield_data:charvalue',
                'value' => 'privacy:metadata:customfield_data:value',
                'valueformat' => 'privacy:metadata:customfield_data:valueformat',
                'timecreated' => 'privacy:metadata:customfield_data:timecreated',
                'timemodified' => 'privacy:metadata:customfield_data:timemodified',
                'contextid' => 'privacy:metadata:customfield_data:contextid',
            ],
            'privacy:metadata:customfield_data'
        );

        // Link to subplugins.
        $collection->add_plugintype_link('customfield', [], 'privacy:metadata:customfieldpluginsummary');

        $collection->link_subsystem('core_files', 'privacy:metadata:filepurpose');

        return $collection;
    }

    /**
     * Export one field data in a component
     *
     * @param \context $context
     * @param \stdClass $fieldobj record from DB table {customfield_field}
     * @param \stdClass $dataobj record from DB table {customfield_data}
     */
    protected static function export_customfield_data($context, $fieldobj, $dataobj) {
        $value = (object)[
            'field' => [
                // Name and description are displayed in mod_data without applying format_string().
                'name' => $fieldobj->name,
                'description' => $fieldobj->description,
                'type' => $fieldobj->type,
                'required' => transform::yesno($fieldobj->required),
            ],
            'value' => $dataobj->value
        ];
        foreach (['intvalue', 'decvalue', 'shortcharvalue', 'charvalue'] as $key) {
            if ($contentobj->$key !== null) {
                $value->$key = $contentobj->$key;
            }
        }
        $classname = manager::get_provider_classname_for_component('customfield_' . $fieldobj->type);
        if (class_exists($classname) && is_subclass_of($classname, customfield_provider::class)) {
            component_class_callback($classname, 'export_customfield_data', [$context, $fieldobj, $dataobj, $value]);
        } else {
            // Custom field plugin does not implement customfield_provider, just export default value.
            writer::with_context($context)->export_data([$fieldobj->id, $dataobj->id], $value);
        }
        writer::with_context($context)->export_area_files([$fieldobj->id, $dataobj->id], 'core_customfield', 'customfield_data', $dataobj->id);
    }

    /**
     * Export basic info about custom fields
     *
     * @param \context $context
     * @param \stdClass $user
     */
    protected static function export_field($context, $user) {
        if (!$context) {
            return;
        }
        $contextdata = helper::get_context_data($context, $user);
        helper::export_context_files($context, $user);
        writer::with_context($context)->export_data([], $contextdata);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        $datatobedeleted = [];

        $sql = "SELECT " . self::sql_fields() . "
                  FROM {customfield_data} cfd
                  JOIN {customfield_field} cff
                    ON (cff.id = cfd.fieldid)
                 WHERE cfd.contextid = :contextid
              ORDER BY cfd.id";
        $rs = $DB->get_recordset_sql($sql, ['contextid' => $context->id]);
        foreach ($rs as $row) {
            self::mark_customfield_data_for_deletion($context, $row);
            $datatobedeleted[$row->dataid] = $row->dataid;
        }
        $rs->close();

        self::delete_customfield_data($context, $datatobedeleted);
    }

    /**
     * Marks a customfield_data for deletion
     *
     * Also invokes callback from customfield plugin in case it stores additional data that needs to be deleted
     *
     * @param \context $context
     * @param \stdClass $row result of SQL query - tables customfield_data, customfield_field join together
     */
    protected static function mark_customfield_data_for_deletion($context, $row) {
        if ($row->dataid && $row->fieldid) {
            $fieldobj = self::extract_object_from_record($row, 'field', ['field' => $row->fieldid]);
            $dataobj = self::extract_object_from_record($row, 'data', ['fieldid' => $fieldobj->id]);

            // Allow datafield plugin to implement their own deletion.
            $classname = manager::get_provider_classname_for_component('customfield_' . $fieldobj->type);
            if (class_exists($classname) && is_subclass_of($classname, customfield_provider::class)) {
                component_class_callback($classname, 'delete_customfield_data', [$context, $fieldobj, $dataobj]);
            }
        }
    }

    /**
     * Deletes records marked for deletion and all associated data
     *
     * Should be executed after all records were marked by {@link mark_data_content_for_deletion()}
     *
     * Deletes records from customfield_data table and associated files
     *
     * @param \context $context
     * @param array $datatobedeleted list of ids of the customfield_data that need to be deleted
     */
    protected static function delete_customfield_data($context, $datatobedeleted) {
        global $DB;
        if (empty($recordstobedeleted)) {
            return;
        }

        list($sql, $params) = $DB->get_in_or_equal($datatobedeleted, SQL_PARAMS_NAMED);

        // Delete files.
        get_file_storage()->delete_area_files_select($context->id, 'core_customfield', 'customfield_data',
            "IN (SELECT cfd.id FROM {customfield_data} cfd WHERE cfd.fieldid $sql)", $params);
        // Delete from data_content.
        $DB->delete_records_select('customfield_data', 'fieldid ' . $sql, $params);
    }

    /**
     * SQL query that returns all fields from {customfield_data}, {data_fields} and {data_records} tables
     *
     * @return string
     */
    protected static function sql_fields() {
        return 'cfd.instanceid, cfd.intvalue AS dataintvalue, cfd.decvalue AS datadecvalue, cfd.shortcharvalue AS datashortcharvalue,
                cfd.charvalue AS datacharvalue, cfd.value AS datavalue, cfd.valueformat AS datavalueformat,
                cfd.timecreated AS datatimecreated, cfd.timemodified AS datatimemodified,
                cff.shortname AS fieldshortname, cff.name AS fieldname, cff.type AS fieldtype, cff.description AS fielddescription,
                cff.descriptionformat AS descriptionformat, cff.sortorder AS fieldsortorder, cff.categoryid AS fieldcategoryid,
                cff.configdata AS fieldconfigdata, cff.timecreated AS fieldtimecreated, cff.timemodified AS fieldtimemodified ';
    }

    /**
     * Creates an object from all fields in the $record where key starts with $prefix
     *
     * @param \stdClass $record
     * @param string $prefix
     * @param array $additionalfields
     * @return \stdClass
     */
    protected static function extract_object_from_record($record, $prefix, $additionalfields = []) {
        $object = new \stdClass();
        foreach ($record as $key => $value) {
            if (preg_match('/^'.preg_quote($prefix, '/').'(.*)/', $key, $matches)) {
                $object->{$matches[1]} = $value;
            }
        }
        if ($additionalfields) {
            foreach ($additionalfields as $key => $value) {
                $object->$key = $value;
            }
        }
        return $object;
    }
}
