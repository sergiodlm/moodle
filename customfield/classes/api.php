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

use core\output\inplace_editable;

defined('MOODLE_INTERNAL') || die;

class api {

    /**
     * Fetch a field from database or create a new one if $field is given
     *
     * @param int $id id of the field (0 for new field)
     * @param \stdClass|null $field
     * @return field
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_field(int $id, \stdClass $field = null): field {
        return field::load_field($id, $field);
    }

    /**
     * Fetch a data from database or create a new one if $data is given
     *
     * @param int $id id of the field (0 for new field)
     * @param \stdClass $data a pre-fetched data
     * @param field $field a pre-fetched field
     * @return data
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function load_data(int $id, \stdClass $data, field $field): data {
        return data::load_data($id, $data, $field);
    }

    /**
     * Retrieves list of all fields and the data associated with them
     *
     * @param array $fields
     * @param \context $datacontext context to use for data that does not yet exist
     * @param int $instanceid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_fields_with_data(array $fields, \context $datacontext, int $instanceid): array {
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
                    ON (f.id = d.fieldid AND d.instanceid = :instanceid)
                 WHERE f.id {$sqlfields}
              ORDER BY c.sortorder, f.sortorder DESC";
        $params['instanceid'] = $instanceid;
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
                $data->instanceid  = $instanceid;
            }
            $formfields[] = self::load_data(0, $data, $field);
        }
        return $formfields;
    }

    /**
     * Retrieves list of fields and the data associated with them for backups
     *
     * @param array $fields
     * @param \context $datacontext context to use for data that does not yet exist
     * @param int $instanceid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_fields_with_data_for_backup(array $fields, \context $datacontext, int $instanceid): array {
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
                    ON (f.id = d.fieldid AND d.instanceid = :instanceid)
                 WHERE f.id {$sqlfields}
              ORDER BY c.sortorder, f.sortorder";
        $params['instanceid'] = $instanceid;
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
                $data->instanceid  = $instanceid;
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
     * @throws \coding_exception
     */
    public static function field_types() {
        $fieldtypes = array();

        $plugins = \core\plugininfo\customfield::get_enabled_plugins();
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

        if (($field->get('type') == 'textarea') && isset($formdata->configdata['defaultvalue']['text'])) {

            // Find context.
            $context                = \context_system::instance();
            $textoptions['context'] = $context;

            // Store files.
            $data = (object) ['defaultvalue_editor' => $formdata->configdata['defaultvalue']];

            $data = file_postupdate_standard_editor($data, 'defaultvalue', $textoptions, $context,
                'core_customfield', 'defaultvalue_editor', $field->get('id'));

            $configdata = $field->get('configdata');
            if ($configdata) {
                $configdata['defaultvalue']['text'] = $data->defaultvalue;
                $field->set('configdata', json_encode($configdata));
                $field->save();
            }
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
     * Get the custom fields and data from a context.
     *
     * @param $contextid
     *
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function get_fields_with_data_fromcontext($contextid): array {
        global $DB;

        $sql = "SELECT f.id as field_id, f.shortname, f.categoryid, f.type, f.configdata,
                       c.name as categoryname, d.*
                  FROM {customfield_category} c
                  JOIN {customfield_field} f
                    ON (c.id = f.categoryid)
             LEFT JOIN {customfield_data} d
                    ON (f.id = d.fieldid AND d.contextid = :contextid)
              ORDER BY c.sortorder, f.sortorder DESC";

        $params['contextid'] = $contextid;
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
                $data->contextid = $contextid;
            }
            $formfields[] = self::load_data($data->id, $data, $field);
        }
        return $formfields;
    }

    /**
     * Update sort order of the fields
     *
     * @param void
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function reorder_fields(category $category): bool {
        global $DB;

        $fieldneighbours = $DB->get_records(field::TABLE, ['categoryid' => $category->get('id')], 'sortorder DESC');

        $neworder = count($fieldneighbours);

        foreach ($fieldneighbours as $field) {
            $dataobject            = new \stdClass();
            $dataobject->id        = $field->id;
            $dataobject->sortorder = --$neworder;
            if (!$DB->update_record(field::TABLE, $dataobject)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns an object for inplace editable
     *
     * @param bool $editable
     * @return inplace_editable
     * @throws \coding_exception
     */
    public static function get_category_inplace_editable(category $category, bool $editable = true) : inplace_editable {
        return new inplace_editable('core_customfield',
                                    'category',
                                    $category->get('id'),
                                    $editable,
                                    format_string($category->get('name')),
                                    $category->get('name'),
                                    get_string('editcategoryname', 'core_customfield'),
                                    get_string('newvaluefor', 'core_form', format_string($category->get('name')))
        );
    }

    /**
     * Updates sortorder (used on drag and drop)
     *
     * @param $options
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function reorder_categories($component, $area, $itemid): bool {
        $categoryneighbours = api::list_categories($component, $area, $itemid);

        // First let's move the new element at the end of categories list.
        $lastcategorysortorder = 0;
        foreach ($categoryneighbours as $category) {
            if ($category->get('sortorder') > $lastcategorysortorder) {
                $lastcategorysortorder = $category->get('sortorder');
            }
        }
        foreach ($categoryneighbours as $category) {
            if ($category->get('sortorder') < 0) {
                $category->set('sortorder', $lastcategorysortorder+1);
            }
        }

        // And now let's update sortorder values in the database.
        $sortfunction = function(category $a, category $b): int {
            return $a->get('sortorder') <=> $b->get('sortorder');
        };

        usort($categoryneighbours, $sortfunction);

        foreach ($categoryneighbours as $sortorder => $category) {
            $category->set('sortorder', $sortorder);
            $category->save();
        }

        return true;
    }

    /**
     * Backend function for Drag and Drop
     *
     * @param $from
     * @param $to
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function move_category(int $category, int $beforeid = 0) {
        $categoryfrom = new category($category);
        $categoryto   = new category($beforeid);

        // Move to the last position on the list.
        if ($beforeid === 0) {
            $categoryfrom->set('sortorder', -1);
            $categoryfrom->save();
            return api::reorder_categories($categoryfrom->get('component'), $categoryfrom->get('area'), $categoryfrom->get('itemid'));
        }

        if ($categoryfrom->get('sortorder') < $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i < $categoryto->get('sortorder')-1; $i++) {
                self::change_category_position($categoryfrom, 1);
            }
        } else if ($categoryfrom->get('sortorder') > $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i > $categoryto->get('sortorder'); $i--) {
                self::change_category_position($categoryfrom, -1);
            }
        }

        return true;
    }

    /**
     * Move a category (used by drag and drop)
     *
     * @param int $position
     * @return category
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    private static function change_category_position(category $category, int $position): category {
        $nextcategory = api::list_categories(
                $category->get('component'),
                $category->get('area'),
                $category->get('itemid'),
                $category->get('sortorder'
                ) + $position)[0];

        if (!empty($nextcategory)) {
            $nextcategory->set('sortorder', $category->get('sortorder'));
            $nextcategory->save();
            $category->set('sortorder', $category->get('sortorder') + $position);
            $category->save();
        }
        return $category;
    }

    /**
     * Returns a list of categories with their related fields.
     *
     * @return category[]
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function list_categories($component, $area, $itemid, $sortorder = 0): array {
        global $DB;

        $categories = array();

        $options = [
                'component' => $component,
                'area'      => $area,
                'itemid'    => $itemid
        ];

        if ($sortorder !== 0) {
            $options['sortorder'] = $sortorder;
        }

        foreach ($DB->get_records(category::TABLE, $options, 'sortorder') as $categorydata) {
            $categories[] = new category(0, $categorydata);
        }

        return $categories;
    }

    /**
     * Call the specified callback method of the field plugin
     *
     * If the callback returns null, then the default value is returned instead.
     * If the class does not exist, then the default value is returned.
     *
     * @param   field       $field
     * @param   string      $methodname The name of the staticically defined method on the class.
     * @param   array       $params The arguments to pass into the method.
     * @param   mixed       $default The default value.
     * @return  mixed       The return value.
     */
    protected static function plugin_callback(field $field, string $methodname, array $params, $default = null) {
        $classname = '\\customfield_' . $field->get('type') . '\\plugin';
        if (!class_exists($classname)) {
            // There could be data in the database that belongs to the type that was deleted.
            // Do not throw exception. Show developer warning only.
            debugging("Class {$classname} is not found", DEBUG_DEVELOPER);
            return $default;
        } else if (!is_subclass_of($classname, plugin_base::class)) {
            debugging("Class {$classname} must extend " . plugin_base::class, DEBUG_DEVELOPER);
            return $default;
        } else {
            return component_class_callback($classname, $methodname, $params, $default);
        }
    }

    /**
     * Allows to add elements to the field configuration form
     *
     * @param field $field
     * @param \MoodleQuickForm $mform
     */
    public static function add_field_to_config_form(field $field, \MoodleQuickForm $mform) {
        self::plugin_callback($field, 'add_field_to_config_form', [$field, $mform]);
    }
}
