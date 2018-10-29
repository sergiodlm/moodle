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
 * @copyright 2018, Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use core\persistent;
use stdClass;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package core_customfield
 */
class data extends persistent {

    /**
     * Database data.
     */
    const TABLE = 'customfield_data';

    /**
     * Field that this data belongs to.
     *
     * @var field
     */
    protected $field;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'fieldid'        => [
                        'type' => PARAM_TEXT,
                ],
                'instanceid'       => [
                        'type' => PARAM_TEXT,
                ],
                'intvalue'       => [
                        'type'     => PARAM_INT,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
                'decvalue'       => [
                        'type'     => PARAM_FLOAT,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
                'charvalue'      => [
                        'type'     => PARAM_TEXT,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
                'shortcharvalue' => [
                        'type'     => PARAM_TEXT,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
                // Mandatory field.
                'value'          => [
                        'type'    => PARAM_RAW,
                        'null'    => NULL_NOT_ALLOWED,
                        'default' => ''
                ],
                // Mandatory field.
                'valueformat'    => [
                        'type'    => PARAM_INT,
                        'null'    => NULL_NOT_ALLOWED,
                ],
                'contextid'      => [
                        'type'     => PARAM_INT,
                        'optional' => false,
                        'null'     => NULL_NOT_ALLOWED
                ]
        );
    }

    /**
     * Creates an instance of class
     *
     * @param int $id
     * @param \stdClass $data
     * @param field $field
     * @return data
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function __construct($id = 0, \stdClass $record = null) {

        $customdatatype = "\\customfield_{$record->fieldtype}\\data";
        if (!class_exists($customdatatype) || !is_subclass_of($customdatatype, data::class)) {
            throw new \moodle_exception(get_string('errordatatypenotfound', 'core_customfield', s($fieldtype)));
        }
        return parent::__construct($id, $record);
    }

    /**
     * @param int $fieldid
     * @return data
     * @throws \dml_exception
     */
    public static function fieldload(int $fieldid): self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid]);

        if ($dbdata) {
            return new static($dbdata->id);
        } else {
            return new static();
        }
    }

    /**
     * Set the field associated with this data
     *
     * @param field $field
     */
    public function set_field(field $field) {
        $this->field = $field;
    }

    /**
     * Field associated with this data
     *
     * @return field
     */
    public function get_field(): field {
        return $this->field;
    }

    /**
     * Save the value to be used/submitted on form
     *
     * @param $value
     * @throws \moodle_exception
     */
    public function set_formvalue($value) {
        $this->set(api::datafield($this->get_field()), $value->{api::datafield($this->get_field())});
    }

    /**
     * Save the value from backup
     *
     * @param $value
     * @throws \moodle_exception
     */
    public function set_rawvalue($value) {
        $this->set(api::datafield($this->get_field()), $value);
    }

    /**
     * Return the default value if the field has not been set.
     *
     * @return mixed
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_charvalue() {
        if ($this->get('id') == 0) {
            return $this->get_field()->get_configdata_property('defaultvalue');
        }
        return $this->raw_get('charvalue');
    }

    /**
     * Return the default value if the field has not been set.
     * Work with checkbox field and select field.
     *
     * @return mixed
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_intvalue() {
        $type = $this->field->get('type');
        if ($this->get('id') == 0 && $type == 'checkbox') {
            return $this->get_field()->get_configdata_property('checkbydefault');
        }
        if ($this->get('id') == 0 && $type == 'select') {
            $configoptions = $this->get_field()->get_configdata_property('options');
            $options = explode("\r\n", $configoptions);
            $defaultvalue = $this->get_field()->get_configdata_property('defaultvalue');
            return array_search($defaultvalue, $options);
        }
        return $this->raw_get('intvalue');
    }

    /**
     * Return the default value if the field has not been set.
     *
     * @return mixed
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    protected function get_value() {
        $type = $this->field->get('type');
        if ($this->get('id') == 0 && $type == 'textarea') {
            $defaultvalue = $this->get_field()->get_configdata_property('defaultvalue');
            return $defaultvalue;
        }
        return $this->raw_get('value');
    }

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function edit_save_data(\stdClass $datanew) {
        $value = $datanew->{api::field_inputname($this->get_field())};
        $this->set(api::datafield($this->get_field()), $value);
        $this->set('value', $value);
        $this->save();
        return $this;
    }

    /**
     * Adds data in a given object on api::field_inputname() attribute.
     *
     * @param \stdClass $data
     * @throws \moodle_exception
     */
    public function add_customfield_data_to_object(\stdClass $data) {
        $data->{api::field_inputname($this->get_field())} = $this->get(api::datafield($this->get_field()));
    }

    /**
     * Validates data for this field.
     *
     * @param \stdClass $data
     * @param array $files
     * @return array
     */
    public function validate_data(\stdClass $data, array $files): array {
        global $DB;

        $errors = [];
        if ($this->get_field()->get_configdata_property('uniquevalues') == 1) {

            $datafield = api::datafield($this->get_field());
            $where = "fieldid = ? AND {$datafield} = ?";
            $params = [$this->get_field()->get('id'), $data->{api::field_inputname($this->get_field())}];
            if (isset($data->id) && $data->id > 1) {
                $where .= ' AND instanceid != ?';
                $params[] = $data->id;
            }
            if ($DB->record_exists_select('customfield_data', $where, $params)) {
                $errors[api::field_inputname($this->get_field())] = get_string('erroruniquevalues', 'core_customfield');
            }
        }
        return $errors;
    }

    /**
     * Tweaks the edit form.
     *
     * @param \MoodleQuickForm $mform
     * @return bool
     * @throws \moodle_exception
     */
    public function edit_after_data(\MoodleQuickForm $mform): bool {
        return true;
    }

    /**
     * Returns field as a renderable object. Used by handlers to display data on various places.
     * @return string
     */
    public function display() {
        $type = $this->get_field()->get('type');
        $classpath = "\\customfield_{$type}\\output\\display";
        return new $classpath($this);
    }

    /**
     * Return the context of the field
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_context() : \context {
        return \context::instance_by_id($this->get('contextid'));
    }
}
