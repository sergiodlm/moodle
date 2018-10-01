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
    public static function get_fields_definitions(string $component, string $area, int $itemid) : array {
        return category::list([
                'component' => $component,
                'area' => $area,
                'itemid' => $itemid
        ]);
    }

    public static function get_field(int $id, \stdClass $record = null) : field {
        return field_factory::load($id, $record);
    }

    public static function load_data(\stdClass $data, field $field) : data {
        return data_factory::load($data, $field);
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
    public static function get_fields_with_data(string $component, string $area, int $itemid, \context $datacontext, int $recordid) : array {
        global $DB;
        $sql = 'SELECT f.id as field_id, f.shortname, f.categoryid, f.type, f.configdata,
                       c.name as categoryname, d.*
                  FROM {customfield_category} c
                  JOIN {customfield_field} f
                    ON (c.id = f.categoryid)
             LEFT JOIN {customfield_data} d
                    ON (f.id = d.fieldid AND d.recordid = :recordid)
                 WHERE c.component = :component
                   AND c.area = :area
                   AND c.itemid = :itemid
              ORDER BY c.sortorder, f.sortorder';
        $where = ['component' => $component, 'area' => $area, 'itemid' => $itemid, 'recordid' => $recordid];
        $fieldsdata = $DB->get_records_sql($sql, $where);

        $formfields = [];
        foreach ($fieldsdata as $data) {
            $fieldobj = (object)['id' => $data->field_id, 'shortname' => $data->shortname, 'type' => $data->type,
                'configdata' => $data->configdata, 'categoryid' => $data->categoryid];
            $field = self::get_field(0, $fieldobj);
            $categoryobj = (object)['id' => $data->categoryid, 'name' => $data->categoryname,
                'component' => $component, 'area' => $area, 'itemid' => $itemid];
            $field->set_category(new category(0, $categoryobj));
            unset($data->field_id, $data->shortname, $data->type, $data->categoryid, $data->configdata, $data->categoryname);
            if (empty($data->id)) {
                $data->fieldid = $field->get('id');
                $data->contextid = $datacontext->id;
                $data->recordid = $recordid;
            }
            $formfields[] = self::load_data($data, $field);
        }
        return $formfields;
    }

    /**
     * Retrieve a list of all available custom field types
     * @return   array   a list of the fieldtypes suitable to use in a select statement
     */
    public static function field_types() {
        $fieldtypes = array();

        $plugins = \core_component::get_plugin_list('customfield');
        foreach ($plugins as $type => $unused) {
            $fieldtypes[$type] = get_string('pluginname', 'customfield_'.$type);
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
     */
    public static function save_field(field $field, \stdClass $formdata, array $textoptions) {
        foreach ($formdata as $key => $value) {
            if ($key === 'configdata' && is_array($value)) {
                $value = json_encode($value);
            }
            if ($key === 'id' || ($key === 'type' && $field->get('id'))) {
                continue;
            }
            if (field::has_property($key)) {
                $field->set($key, $value);
            }
        }

        $field->save();

        if (isset($formdata->description_editor)) {
            // Find context.
            $context = \context_system::instance();
            $textoptions['context'] = $context;

            // Store files.
            $data = (object)['description_editor' => $formdata->description_editor];
            $data = file_postupdate_standard_editor($data, 'description', $textoptions, $context,
                'core_customfield', 'description', $field->get('id'));
            $field->set('description', $data->description);
            $field->set('descriptionformat', $data->descriptionformat);
            $field->save();
        }

        // TODO trigger event.
    }

    /**
     * Updates or creates a category with data that came from a form
     *
     * @param category $category
     * @param \stdClass $formdata
     */
    public static function save_category(category $category, \stdClass $formdata) {
        $category->set('name', $formdata->name);
        $category->save();

        // TODO trigger event.
    }
}
