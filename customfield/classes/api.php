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

namespace core_customfield;

defined('MOODLE_INTERNAL') || die;

class api {

    /**
     * Returns array of categories, each of them contains a list of fields definitions.
     *
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @return category[]
     */
    public static function get_fields_definitions(string $component, string $area, int $itemid): array {
        return category::list(['component' => $component, 'area' => $area, 'itemid' => $itemid]);
    }

    /**
     * Fetch a field from database or create a new one if $field is given
     *
     * @param int $id id of the field (0 for new field)
     * @param \stdClass $record a pre-fetched record
     * @return field
     */
    public static function get_field(int $id, \stdClass $field = null): field {
        return field::load_field($id, $field);
    }

    /**
     * Fetch a data from database or create a new one if $data is given
     *
     * @param int $id id of the field (0 for new field)
     * @param \stdClass $data a pre-fetched data
     * @param \stdClass $field a pre-fetched field
     * @return field
     */
    public static function load_data(int $id, \stdClass $data, field $field): data {
        return data::load_data($id, $data, $field);
    }

    /**
     * Retrieves list of all fields and the data associated with them
     *
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param \context $datacontext context to use for data that does not yet exist
     * @param int $recordid
     * @return array
     */
    public static function get_fields_with_data(array $fields, \context $datacontext, int $recordid): array {
        global $DB;

        if (empty($fields)) {
            return array();
        }

        list($sqlfields, $params) = $DB->get_in_or_equal(array_keys($fields), SQL_PARAMS_NAMED);
        $sql = "SELECT f.id as field_id, f.shortname, f.categoryid, f.type, f.configdata,
                       c.name as categoryname, d.*
                  FROM {customfield_category} c
                  JOIN {customfield_field} f
                    ON (c.id = f.categoryid)
             LEFT JOIN {customfield_data} d
                    ON (f.id = d.fieldid AND d.recordid = :recordid)
                 WHERE f.id {$sqlfields}
              ORDER BY c.sortorder, f.sortorder";
        $params['recordid'] = $recordid;
        $fieldsdata = $DB->get_records_sql($sql, $params);

        $formfields = [];
        foreach ($fieldsdata as $data) {
            $fieldobj    = (object) ['id'         => $data->field_id, 'shortname' => $data->shortname, 'type' => $data->type,
                                     'configdata' => $data->configdata, 'categoryid' => $data->categoryid];
            $field       = self::get_field(0, $fieldobj);
            $categoryobj = (object) ['id' => $data->categoryid, 'name' => $data->categoryname];
            $field->set_category(new category(0, $categoryobj));
            unset($data->field_id, $data->shortname, $data->type, $data->categoryid, $data->configdata, $data->categoryname);
            if (empty($data->id)) {
                $data->id        = 0;
                $data->fieldid   = $field->get('id');
                $data->contextid = $datacontext->id;
                $data->recordid  = $recordid;
            }
            $formfields[] = self::load_data($data->id, $data, $field);
        }
        return $formfields;
    }

    /**
     * Retrieves list of fields and the data associated with them for backups
     *
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @param \context $datacontext context to use for data that does not yet exist
     * @param int $recordid
     * @return array
     */
    public static function get_fields_with_data_for_backup(array $fields, \context $datacontext, int $recordid): array {
        global $DB;

        if (empty($fields)) {
            return array();
        }

        list($sqlfields, $params) = $DB->get_in_or_equal(array_keys($fields), SQL_PARAMS_NAMED);
        $sql = "SELECT f.id as field_id, f.shortname, f.type, f.categoryid, f.configdata, d.*
                  FROM {customfield_category} c
                  JOIN {customfield_field} f
                    ON (c.id = f.categoryid)
                  JOIN {customfield_data} d
                    ON (f.id = d.fieldid AND d.recordid = :recordid)
                 WHERE f.id {$sqlfields}
              ORDER BY c.sortorder, f.sortorder";
        $params['recordid'] = $recordid;
        $fieldsdata = $DB->get_records_sql($sql, $params);

        $finalfields = [];
        foreach ($fieldsdata as $data) {
            $fieldobj = (object) ['id'   => $data->field_id, 'shortname' => $data->shortname,
                                  'type' => $data->type, 'categoryid' => $data->categoryid, 'configdata' => $data->configdata];
            $field    = self::get_field(0, $fieldobj);
            unset($data->field_id, $data->shortname, $data->type, $data->categoryid, $data->configdata);
            if (empty($data->id)) {
                $data->fieldid   = $field->get('id');
                $data->contextid = $datacontext->id;
                $data->recordid  = $recordid;
            }
            $f             = self::load_data($data->id, $data, $field);
            $finalfields[] = ['id'   => $f->get('id'), 'shortname' => $f->get_field()->get('shortname'),
                              'type' => $f->get_field()->get('type'), 'value' => $f->get_formvalue()];
        }
        return $finalfields;
    }

    /**
     * Retrieve a list of all available custom field types
     *
     * @return   array   a list of the fieldtypes suitable to use in a select statement
     */
    public static function field_types() {
        $fieldtypes = array();

        $plugins = \core_component::get_plugin_list('customfield');
        foreach ($plugins as $type => $unused) {
            $fieldtypes[$type] = get_string('pluginname', 'customfield_' . $type);
        }
        asort($fieldtypes);

        return $fieldtypes;
    }

    /**
     * Updates or creates a field with data that came from a form
     *
     * @param field $field
     * @param \stdClass $formdata
     * @param array $textoptions editor options (trusttext, subdirs, maxfiles, maxbytes etc.)
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function save_field(field $field, \stdClass $formdata, array $textoptions) {
        foreach ($formdata as $key => $value) {
            if ($key === 'configdata' && is_array($formdata->configdata)) {
                $field->set($key, json_encode($value));
                continue;
            }
            if ($key === 'id' || ($key === 'type' && $field->get('id'))) {
                continue;
            }
            if (field::has_property($key)) {
                $field->set($key, $value);
            }
        }

        $created = !$field->get('id');
        $field->save();

        if (isset($formdata->description_editor)) {
            // Find context.
            $context                = \context_system::instance();
            $textoptions['context'] = $context;

            // Store files.
            $data = (object) ['description_editor' => $formdata->description_editor];
            $data = file_postupdate_standard_editor($data, 'description', $textoptions, $context,
                                                    'core_customfield', 'description', $field->get('id'));
            $field->set('description', $data->description);
            $field->set('descriptionformat', $data->descriptionformat);
            $field->save();
        }

        $eventparams = ['objectid' => $field->get('id'), 'context' => \context_system::instance(),
                'other' => ['shortname' => $field->get('shortname'), 'name' => $field->get('name')]];
        if ($created) {
            $event = \core_customfield\event\field_created::create($eventparams);
        } else {
            $event = \core_customfield\event\field_updated::create($eventparams);
        }
        $event->trigger();
    }

    /**
     * Updates or creates a category with data that came from a form
     *
     * @param category $category
     * @param \stdClass $formdata
     */
    public static function save_category(category $category, \stdClass $formdata) {
        $created = !$field->get('id');

        $category->set('name', $formdata->name);
        $category->save();

        $eventparams = ['objectid' => $category->get('id'), 'context' => \context_system::instance(),
                        'other' => ['name' => $category->get('name')]];
        if ($created) {
            $event = \core_customfield\event\category_created::create($eventparams);
        } else {
            $event = \core_customfield\event\category_updated::create($eventparams);
        }
        $event->trigger();
    }
}
