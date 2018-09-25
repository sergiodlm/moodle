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

    private $itemid;
    private $component;
    private $area;

    public final function __construct($itemid = null) {
        if (!preg_match('|^(\w+_[\w_]+)\\\\customfield\\\\([\w_]+)_handler$|', static::class, $matches)) {
            throw new \coding_exception('Handler class name must have format: <PLUGIN>\\customfield\\<AREA>_handler');
        }
        $this->component = $matches[1];
        $this->area = $matches[2];
        $this->itemid = $itemid ?: null;
    }

    /**
     * Returns an instance of handler by it's class name
     *
     * @param string $classname
     * @return handler
     * @throws \moodle_exception
     */
    public static function get_handler(string $component, string $area, int $itemid = null) : handler {
        $classname = $component . '\\customfield\\' . $area . '_handler';
        if (class_exists($classname) && is_subclass_of($classname, self::class)) {
            return new $classname($itemid);
        }
        $a = ['component' => s($component), 'area' => s($area)];
        throw new \moodle_exception('unknownhandler', 'core_customfield', (object)$a);
    }

    public static function get_handler_for_field(field $field) : handler {
        $category = new category($field->get('categoryid'));
        return self::get_handler_for_category($category);
    }

    public static function get_handler_for_category(category $category) : handler {
        return self::get_handler($category->get('component'), $category->get('area'), $category->get('itemid'));
    }

    public function get_component() : string {
        return $this->component;
    }

    public function get_area() : string {
        return $this->area;
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context
     */
    abstract public function get_configuration_context() : \context;

    abstract public function get_configuration_url() : \moodle_url;

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

    public function get_category_config_form(category $category): category_config_form {
        $form = new category_config_form(null, ['handler' => $this, 'category' => $category]);
        $form->set_data($this->prepare_category_for_form($category));
        return $form;
    }

    public function get_field_config_form(field $field): field_config_form {
         $form = new field_config_form(null, ['handler' => $this, 'field' => $field]);
         $form->set_data($this->prepare_field_for_form($field));
         return $form;
    }

    public function new_field(category $category, string $type) : field {
        $field = field_factory::create($type);
        $field->set('categoryid', $category->get('id'));
        return $field;
    }

    public function new_category() : category {
        $categorydata = new stdClass();
        $categorydata->component = $this->get_component();
        $categorydata->area = $this->get_area();
        $categorydata->itemid = $this->get_item_id();
        $categorydata->contextid = $this->get_configuration_context()->id;

        $category = new category(0, $categorydata);

        return $category;
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
            $categorylist[$category->get('id')] = $category->get('name');
        }
        return $categorylist;
    }

    abstract public function can_configure(): bool;

    abstract public function can_edit($recordid = null): bool;

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
            $categories[$field->get('categoryid')][] = $field;
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

    /**
     * Options for processing embedded files in the field description.
     *
     * Handlers may want to extend it to disable files support and/or specify 'noclean'=>true
     * Context is not necessary here
     *
     * @return array
     */
    public function get_description_text_options() {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
        ];
    }

    /**
     * Save the field configuration with the data from the form
     *
     * @param field $field
     * @param stdClass $data data from the form
     */
    public function save_field(field $field, stdClass $data) {
        try {
            api::save_field($field, $data, $this->get_description_text_options());
        \core\notification::success(get_string('fieldsaved', 'core_customfield'));
        } catch (\moodle_exception $exception) {
            \core\notification::error(get_string('fieldsavefailed', 'core_customfield'));
        }
    }

    /**
     * Prepare the field data to set in the configuration form
     *
     * @param field $field
     * @return stdClass
     */
    protected function prepare_field_for_form(field $field) : stdClass {
        $data = $field->to_record();
        $context = $this->get_configuration_context();
        $textoptions = ['context' => $context] + $this->get_description_text_options();
        $data->configdata = json_decode($data->configdata, true);
        if ($data->id) {
            file_prepare_standard_editor($data, 'description', $textoptions, $context, 'core_customfield',
                'description', $data->id);
        }

        return $data;
    }

    /**
     * Prepare category data to set in the configuration form
     *
     * @param category $category
     * @return stdClass
     */
    protected function prepare_category_for_form(category $category) : stdClass {
        return $category->to_record();
    }

    /**
     * Save the category configuration using the data from the form
     *
     * @param category $category
     * @param stdClass $data data from the form
     */
    public function save_category(category $category, stdClass $data) {
        try {
            api::save_category($category, $data);
            \core\notification::success(get_string('categorysaved', 'core_customfield'));
        } catch (\moodle_exception $exception) {
            \core\notification::error(get_string('categorysavefailed', 'core_customfield'));
        }
    }
}
