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

use stdClass;

defined('MOODLE_INTERNAL') || die;

abstract class handler {

    protected $itemid;

    public final function __construct($itemid = null) {
        $this->itemid = $itemid ?: null;
    }

    /**
     * Returns an instance of handler by it's class name
     *
     * @param string $classname
     * @return handler
     * @throws \moodle_exception
     */
    public static function get_instance(string $classname, int $itemid = null) : handler {
        if (class_exists($classname) && is_subclass_of($classname, self::class)) {
            return new $classname($itemid);
        }
        throw new \moodle_exception('unknownhandler', 'core_customfield');
    }

    abstract function get_component() : string;

    abstract function get_area() : string;

    abstract function get_configuration_url() : \moodle_url;

    /**
     * @return int|null
     */
    public function get_item_id() {
        return $this->itemid;
    }

    public function uses_item_id(): bool {
        return false;
    }

    public function uses_categories(): bool {
        return true;
    }

    public function get_category_config_form(): \core_customfield\category_config_form {
        return new \core_customfield\category_config_form(null, ['handler' => $this]);
    }

    public function get_field_config_form($args): \core_customfield\field_config_form {
        return new \core_customfield\field_config_form(null, ['handler' => $this] + $args);
    }

    public function new_category($name) {
        $categorydata = new stdClass();
        $categorydata->name = $name;
        $categorydata->component = $this->get_component();
        $categorydata->area = $this->get_area();
        $categorydata->itemid = $this->get_item_id();

        $category = new category(0, $categorydata);

        return $category;
    }

    public function load_category($id) {
        return new \core_customfield\category($id);
    }

    public function categories_list() {
        $options = [
                'component' => $this->get_component(),
                'area' => $this->get_area(),
                'itemid' => $this->get_item_id()
        ];

        return \core_customfield\category::list($options);
    }

    public function categories_list_for_select() {
        $categorylist = array();
        foreach ($this->categories_list() as $category) {
            $categorylist[$category->id()] = $category->name();
        }
        return $categorylist;
    }

    abstract public function can_configure($itemid = null): bool;

    abstract public function can_edit($recordid = null, $itemid = null): bool;

    public function is_field_supported(\core_customfield\field $field): bool {
        // Placeholder for now to allow in the future components to decide that they don't want to support some field types.
        return true;
    }

    /**
     * Returns array of categories, each of them contains a list of fields definitions.
     *
     * @param string $component
     * @param string|null $area
     * @param int|null $itemid
     * @return category[]
     */
    public function get_fields_definitions() : array {
        $fields = api::get_fields_definitions(
                $this->get_component(),
                $this->get_area(),
                $this->get_item_id()
        );
        return $fields;
        // return array_filter($fields, [$this, 'is_field_supported']);
    }

    public function get_fields_with_data($recordid) {
        return api::get_fields_with_data($this->get_component(), $this->get_area(), $recordid);
    }

    /**
     * Custom fields definition after data
     *
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
        $fields = $this->get_fields_with_data($data['id']);
        foreach ($fields as $formfield) {
            $errors += $formfield->edit_validate_field($data, $files);
        }
        return $errors;
    }

    public function load_data($data) {
        if (!isset($data->id)) {
            $data->id = 0;
        }
        $fields = $this->get_fields_with_data($data->id);

        foreach ($fields as $formfield) {
            $formfield->edit_load_data($data);
        }
    }

    public function save_customfield_data($data) {
        $fields = $this->get_fields_with_data($data->id);
        foreach ($fields as $formfield) {
            $formfield->edit_save_data($data);
        }
    }

    /**
     * Adds custom fields to edit forms.
     *
     * @param moodleform $mform
     */
    public function add_custom_fields($mform, $record) {

        if (isset($record->id)) {
            $recordid = $record->id;
        } else {
            $recordid = 0;
        }

        $fieldswithdata = $this->get_fields_with_data($recordid);
        $categories = [];
        foreach ($fieldswithdata as $field) {
            $categories[$field->categoryid()][] = $field;
        }
        foreach ($categories as $categoryid => $fields) {
            // Check first if *any* fields will be displayed.
            $fieldstodisplay = [];

            foreach ($fields as $formfield) {
                if ($formfield->is_editable()) {
                    $fieldstodisplay[] = $formfield;
                }
            }

            if (empty($fieldstodisplay)) {
                continue;
            }

            // Display the header and the fields.
            $mform->addElement('header', 'category_' . $categoryid, format_string($formfield->categoryname()));
            foreach ($fieldstodisplay as $formfield) {
                $formfield->edit_field_add($mform);
                if ($formfield->required()) {
                    $mform->addRule($formfield->inputname(), get_string('fieldrequired', 'core_customfield'), 'required', null, 'client');
                }
                // TODO: move capability check to course handler or get capability from current handler.
                if ($formfield->locked() and !has_capability('moodle/course:update', \context_system::instance())) {
                    $mform->hardFreeze($formfield->inputname());
                }
            }
        }
    }

    public function field_types() {
        return api::field_types();
    }
}
