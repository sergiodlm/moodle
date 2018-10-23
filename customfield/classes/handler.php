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
 * The abstract custom fields handler
 *
 * @package   core_customfield
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use stdClass;

// TODO revise function names, they are difficult to understand now.
// This handler provides callbacks for field configuration form and also allows to add the fields to the entity editing form
// It should be clear from the functions names what they do.
// load_data() loads the multiple fields values from an entity, the function name and arguments are very confusing because we use the
// word 'data' for the data related to individual field and even have class with this name

defined('MOODLE_INTERNAL') || die;

/**
 * The abstract custom fields handler
 *
 * @package core_customfield
 */
abstract class handler {

    /**
     * The component this handler handles
     *
     * @var string $component
     */
    private $component;

    /**
     * The area within the component
     *
     * @var string $area
     */
    private $area;

    /**
     * The id of the item within the area and component

     * @var int $itemid
     */
    private $itemid;

    /**
     * Handler constructor.
     *
     * This constructor is protected. To initiate a class use an appropriate static method:
     * - instance
     * - get_handler
     * - get_handler_for_field
     * - get_handler_for_category
     *
     * @param int $itemid
     * @throws \coding_exception
     */
    protected final function __construct(int $itemid = 0) {
        if (!preg_match('|^(\w+_[\w_]+)\\\\customfield\\\\([\w_]+)_handler$|', static::class, $matches)) {
            throw new \coding_exception('Handler class name must have format: <PLUGIN>\\customfield\\<AREA>_handler');
        }
        $this->component = $matches[1];
        $this->area = $matches[2];
        $this->itemid = $itemid;
    }

    /**
     * Returns an instance of the handler
     *
     * Some areas may choose to use singleton/caching here
     *
     * @param int $itemid
     * @return handler
     */
    public static function instance(int $itemid = 0) : handler {
        return new static($itemid);
    }

    /**
     * Returns an instance of handler by it's class name
     *
     * @param string $component
     * @param string $area
     * @param int $itemid
     * @return handler
     * @throws \moodle_exception
     */
    public static function get_handler(string $component, string $area, int $itemid = 0) : handler {
        $classname = $component . '\\customfield\\' . $area . '_handler';
        if (class_exists($classname) && is_subclass_of($classname, self::class)) {
            return $classname::instance($itemid);
        }
        $a = ['component' => s($component), 'area' => s($area)];
        throw new \moodle_exception('unknownhandler', 'core_customfield', (object)$a);
    }

    /**
     * Return handler for a given field
     *
     * @param field $field
     * @return handler
     * @throws \moodle_exception
     */
    public static function get_handler_for_field(field $field) : handler {
        $category = new category($field->get('categoryid'));
        return self::get_handler_for_category($category);
    }

    /**
     * Return the handler for a given category
     *
     * @param category $category
     * @return handler
     * @throws \moodle_exception
     */
    public static function get_handler_for_category(category $category) : handler {
        return self::get_handler($category->get('component'), $category->get('area'), $category->get('itemid'));
    }

    /**
     * @return string
     */
    public function get_component() : string {
        return $this->component;
    }

    /**
     * @return string
     */
    public function get_area() : string {
        return $this->area;
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context
     */
    abstract public function get_configuration_context() : \context;

    /**
     * URL for configuration of the fields on this handler.
     *
     * @return \moodle_url
     */
    abstract public function get_configuration_url() : \moodle_url;

    /**
     * Context that should be used for data stored for the given record
     *
     * @param int $instanceid
     * @return \context
     */
    abstract public function get_data_context(int $instanceid) : \context;

    /**
     * @return int|null
     */
    public function get_itemid() : int {
        return $this->itemid;
    }

    /**
     * @return bool
     */
    public function uses_itemid(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function uses_categories(): bool {
        return true;
    }

    /**
     * The form to create or edit a field
     *
     * @param field $field
     * @return field_config_form
     * @throws \moodle_exception
     */
    public function get_field_config_form(field $field): field_config_form {
         $form = new field_config_form(null, ['handler' => $this, 'field' => $field]);
         $form->set_data($this->prepare_field_for_form($field));
         return $form;
    }

    /**
     * @param category $category
     * @param string $type
     * @return field
     * @throws \coding_exception
     */
    public function new_field(category $category, string $type) : field {
        $field = field::create_from_type($type);
        $field->set('categoryid', $category->get('id'));
        return $field;
    }

    /**
     * Generates a name for the new category
     */
    protected function generate_category_name($suffix = 0) : string {
        $basename = get_string('otherfields', 'core_customfield');
        return $basename . ($suffix ? (' ' . $suffix) : '');
    }

    /**
     * @return category
     */
    public function new_category() : category {
        $categorydata = new stdClass();
        $categorydata->component = $this->get_component();
        $categorydata->area = $this->get_area();
        $categorydata->itemid = $this->get_itemid();
        $categorydata->contextid = $this->get_configuration_context()->id;

        $category = new category(0, $categorydata);

        $suffix = 0;
        while (true) {
            try {
                $category->set('name', $this->generate_category_name($suffix));
                return $category;
            } catch (\moodle_exception $exception) {

            }
            $suffix++;
        }
    }

    /**
     * The current user can configure custom fields on this component.
     *
     * @return bool
     */
    abstract public function can_configure(): bool;

    /**
     * The current user can edit custom fields on the given record on this component.
     *
     * @param field $field
     * @param null $instanceid
     * @return bool
     */
    abstract public function can_edit(field $field, $instanceid = null): bool;

    /**
     * The current user can view the value of the custom field on the given record on this component.
     *
     * @param field $field
     * @param null $instanceid
     * @return bool
     */
    abstract public function can_view(field $field, $instanceid = null): bool;

    /**
     * The given field is supported on by this handler
     *
     * @param field $field
     * @return bool
     */
    public function is_field_supported(\core_customfield\field $field): bool {
        // TODO: Placeholder for now to allow in the future components to decide that they don't want to support some field types.
        return true;
    }

    /**
     * Returns array of categories, each of them contains a list of fields definitions.
     *
     * @return category[]
     */
    public function get_fields_definitions() : array {
        $cache = \cache::make('core', 'customfield_fields_definitions');
        $key = $this->get_component() . '+' . $this->get_area() . '+' . $this->get_itemid();
        if ($data = $cache->get($key)) {
            $fields = $data;
        } else {
            $fields = api::get_fields_definitions($this->get_component(), $this->get_area(), $this->get_itemid());
            $cache->set($key, $fields);
        }
        return $fields;
    }

    /**
     * List of fields with their data
     *
     * @param int $instanceid
     * @return data[]
     */
    public function get_fields_with_data(array $fields, int $instanceid) : array {
        // TODO this function is always used either with "get visibile fields" or with "get editable fields", should really be simplified.
        return api::get_fields_with_data($fields, $this->get_data_context($instanceid), $instanceid);
    }

    /**
     * List of fields with their data (only fields with data).
     *
     * @param int $instanceid
     * @return array
     */
    public function get_fields_with_data_for_backup(int $instanceid) : array {
        // TODO (by Marina): take another look at it.
        $editablefields = $this->get_editable_fields($instanceid);
        return api::get_fields_with_data_for_backup($editablefields, $this->get_data_context($instanceid), $instanceid);
    }

    /**
     * Custom fields definition after data was submitted on data form
     *
     * @param \MoodleQuickForm $mform
     * @param int $instanceid
     * @throws \moodle_exception
     */
    public function definition_after_data(\MoodleQuickForm $mform, int $instanceid) {
        $editablefields = $this->get_editable_fields($instanceid);
        $fields = $this->get_fields_with_data($editablefields, $instanceid);

        foreach ($fields as $formfield) {
            $formfield->edit_after_data($mform);
        }
    }

    /**
     * Add the field to the $record received
     *
     * $data->customfield_{fieldshortname} = {fieldvalue};
     *
     * @param stdClass $record
     * @param bool $foredit only return editable fields
     * @throws \moodle_exception
     */
    public function load_data(stdClass $record, bool $foredit = false) {
        if (!isset($record->id)) {
            $record->id = 0;
        }
        $fields = $foredit ? $this->get_editable_fields($record->id) : $this->get_visible_fields($record->id);
        $fields = $this->get_fields_with_data($fields, $record->id);

        foreach ($fields as $formfield) {
            $formfield->edit_load_data($record);
        }
    }

    /**
     * Saves the given data for custom fields
     *
     * @param stdClass $data
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function save_customfield_data(stdClass $data) {
        $editablefields = $this->get_editable_fields($data->id);
        $fields = $this->get_fields_with_data($editablefields, $data->id);
        foreach ($fields as $formfield) {
            $formfield->edit_save_data($data);
        }
    }

    /**
     * Validates the given data for custom fields
     *
     * @param stdClass $data
     * @param array $files
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function validate_customfield_data(stdClass $data, array $files) {
        $editablefields = $this->get_editable_fields($data->id);
        $fields = $this->get_fields_with_data($editablefields, $data->id);
        $errors = [];
        foreach ($fields as $formfield) {
            $errors += $formfield->validate_data($data, $files);
        }
        return $errors;
    }

    /**
     * Adds custom fields to edit forms.
     *
     * @param \MoodleQuickForm $mform
     * @param $record
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function add_custom_fields(\MoodleQuickForm $mform, $record) {

        if (isset($record->id)) {
            $instanceid = $record->id;
        } else {
            $instanceid = 0;
        }

        $editablefields = $this->get_editable_fields($instanceid);
        $fieldswithdata = $this->get_fields_with_data($editablefields, $instanceid);
        $categories = [];
        foreach ($fieldswithdata as $data) {
            $categories[$data->get_field()->get('categoryid')][] = $data;
        }
        foreach ($categories as $categoryid => $fields) {
            $formfield = reset($fields);
            $mform->addElement('header', 'category_' . $categoryid, format_string($formfield->get_field()->get_category()->get('name')));
            foreach ($fields as $formfield) {
                $formfield->edit_field_add($mform);
                if ($formfield->get_field()->get_configdata_property('required')) {
                    $mform->addRule($formfield->inputname(), get_string('fieldrequired', 'core_customfield'), 'required', null, 'client');
                }
            }
        }
    }

    /**
     * @return array
     */
    public function field_types() :array {
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
    public function get_description_text_options() : array {
        return [
            'maxfiles' => EDITOR_UNLIMITED_FILES,
        ];
    }

    /**
     * Save the field configuration with the data from the form
     *
     * @param field $field
     * @param stdClass $data data from the form
     * @throws \moodle_exception
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
     * @throws \moodle_exception
     */
    protected function prepare_field_for_form(field $field) : stdClass {
        $data = $field->to_record();
        $context = $this->get_configuration_context();
        $textoptions = ['context' => $context] + $this->get_description_text_options();
        $data->configdata = $field->get('configdata');
        if ($data->id) {
            file_prepare_standard_editor($data, 'description', $textoptions, $context, 'core_customfield',
                'description', $data->id);
            if ($field->get('type') == 'textarea' && $data->configdata['defaultvalue']['text']) {
                $data->defaultvalue = $data->configdata['defaultvalue']['text'];
                $data->defaultvalueformat = $data->configdata['defaultvalue']['format'];
                file_prepare_standard_editor($data, 'defaultvalue', $textoptions, $context, 'core_customfield',
                    'defaultvalue_editor', $data->id);
                $data->configdata['defaultvalue']['text'] = $data->defaultvalue_editor['text'];
            }
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
     * Creates or updates custom field data for a instanceid from backup data.
     *
     * @param int $instanceid
     * @param array $data
     */
    public function restore_field_data_from_backup(int $instanceid, array $data) {
        global $DB;
        if ($fieldrecord = $DB->get_record('customfield_field', ['shortname' => $data['shortname']], 'id,type')) {
            $field = field::create_from_type($fieldrecord->type);
            $field->set('id', $fieldrecord->id);

            $datarecord = $DB->get_record('customfield_data', array('instanceid' => $instanceid, 'fieldid' => $field->get('id')));
            if ($datarecord) {
                $dataobject = data::load_data($datarecord->id, $datarecord, $field);
            } else {
                $dataobject = data::load_data(0, new stdClass(), $field);
            }
            $dataobject->set('instanceid', $instanceid);
            $dataobject->set('fieldid', $field->get('id'));
            $dataobject->set('contextid', $this->get_data_context($instanceid)->id);
            $dataobject->set_rawvalue($data['value']);
            $dataobject->save();
        }
    }

    /**
     * Returns the field name formatted according to configuration context.
     *
     * @param field $field
     * @return string
     */
    public function get_field_formatted_name(field $field): string {
        return format_string($field->get('name'), true, ['context' => $this->get_configuration_context()]);
    }

    /**
     * Add additional fields (properties) to the field configuration form.
     *
     * @param \MoodleQuickForm $mform
     */
    public function add_to_field_config_form(\MoodleQuickForm $mform) {
        return null;
    }

    protected function get_flat_fields_list(callable $filter = null) {
        $categories = $this->get_fields_definitions();
        $fields = [];
        foreach ($categories as $category) {
            foreach ($category->fields() as $field) {
                if ($filter === null || $filter($field)) {
                    $fields[$field->get('id')] = $field;
                }
            }
        }
        return $fields;
    }

    public function get_visible_fields(int $instanceid): array {
        $handler = $this;
        return $this->get_flat_fields_list(function($field) use($handler, $instanceid) {
            return $handler->can_view($field, $instanceid);
        });
    }

    public function get_editable_fields(int $instanceid): array {
        $handler = $this;
        return $this->get_flat_fields_list(function($field) use($handler, $instanceid) {
            return $handler->can_edit($field, $instanceid);
        });
    }
}
