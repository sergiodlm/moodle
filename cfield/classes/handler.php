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
 * @package   core_cfield
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_cfield;

defined('MOODLE_INTERNAL') || die;

abstract class handler {

    protected $itemid;

    public function __construct($itemid = null, $component = null, $area = null) {
        $this->itemid = $itemid;
        $this->component = $component;
        $this->area = $area;
    }

    public function get_component() { return $this->component; }

    public function get_area() { return $this->area; }

    public function get_item_id() { return $this->itemid; }

    public function uses_item_id() : bool { return false; }

    public function uses_categories() : bool { return true; }

    public function get_category_config_form($action,$args) : \core_cfield\category_config_form {
        return new \core_cfield\category_config_form($action,$args);
    }

    public function get_field_config_form($action,$args) : \core_cfield\field_config_form {
        return new \core_cfield\field_config_form($action,$args);
    }

    abstract public function can_configure($itemid = null) : bool;

    abstract public function can_edit($recordid = null, $itemid = null) : bool;

    public function is_field_supported(\core_cfield\field $field) : bool {
        // Placeholder for now to allow in the future components to decide that they don't want to support some field types.
        return true;
    }

    public function get_fields_definitions() {
        $fields = api::get_fields_definitions(
                $this->get_component(),
                $this->get_area(),
                $this->get_item_id()
        );
        return $fields;
        //return array_filter($fields, [$this, 'is_field_supported']);
    }

    public function get_fields_with_data($recordid) {
        return api::get_fields_with_data($this->get_component(), $this->get_area(), $recordid);
    }

    /**
     * Custom fields definition after data
     * @param moodleform $mform
     * @param int $userid
     */
    public function definition_after_data($mform, $recordid) {
        global $CFG;

        $fields = $this->get_fields_with_data($recordid);
        foreach ($fields as $formfield) {
            $formfield->edit_after_data($mform);
        }
    }

    public function validate_data($mform, $data, $files) {
        $errors = [];
        $fields = $this->get_fields_with_data($data['id']);
        foreach ($fields as $formfield) {
            $errors += $formfield->edit_validate_field($data, $files);
        }
        return $errors;
    }

    public function load_data($data) {
        $fields = $this->get_fields_with_data($data->id);
        foreach ($fields as $formfield) {
            $formfield->edit_load_data($data);
        }
    }


    public function save_data($data) {
        $fields = $this->get_fields_with_data($data->id);
        foreach ($fields as $formfield) {
            $formfield->edit_save_data($data);
        }
    }

    /**
     * Adds custom fields to course edit forms.
     * @param moodleform $mform
     */
    public function add_custom_fields($mform) {

        $categories = $this->get_fields_definitions();
        foreach ($categories as $category) {
            // Check first if *any* fields will be displayed.
            $fieldstodisplay = [];

            foreach ($category->get_fields() as $formfield) {
                if ($formfield->is_editable()) {
                    $fieldstodisplay[] = $formfield;
                }
            }
            if (empty($fieldstodisplay)) {
                continue;
            }

            // Display the header and the fields.
            $mform->addElement('header', 'category_'.$category->get_id(), format_string($category->get_name()));
            foreach ($fieldstodisplay as $formfield) {
                $formfield->edit_field($mform);
            }
        }
    }
}
