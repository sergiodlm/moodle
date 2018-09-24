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
     * @param string|null $area
     * @param int|null $itemid
     * @return category[]
     */
    public static function get_fields_definitions(string $component, string $area = null, int $itemid = null) : array {
         return category::list([
                'component' => $component,
                'area' => $area,
                'itemid' => $itemid
        ]);
    }

    public static function get_field(int $id, \stdClass $record = null) : field {
        return field_factory::load($id, $record);
    }

    public static function get_fields_with_data($component, $area, $recordid) {
        global $DB;
        $sql = 'SELECT f.id as field_id, f.shortname, f.categoryid, f.type, c.name as categoryname, d.*
                  FROM {customfield_category} c
                  JOIN {customfield_field} f
                    ON (c.id = f.categoryid)
             LEFT JOIN {customfield_data} d
                    ON (f.id = d.fieldid AND d.recordid = :recordid)
                 WHERE c.component = :component
                   AND c.area = :area';
        $where = ['component' => $component, 'area' => $area, 'recordid' => $recordid];
        $fieldsdata = $DB->get_records_sql($sql, $where);

        $formfields = [];
        foreach($fieldsdata as $data) {
            $field = new \stdclass();
            $field->id = $data->field_id;
            $field->shortname = $data->shortname;
            $formfield = self::get_field($field->id, $field);
            $formfield->set_categoryname($data->categoryname);

            if ($data->id == null) {
                $data->fieldid = $data->field_id;
                $data->recordid = $recordid;
            }
            $formfield->set_data($data);
            $formfields[] = $formfield;
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
            $category = new category($field->get('categoryid'));
            $context = \context::instance_by_id($category->get('contextid'));
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
