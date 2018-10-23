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

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package core_customfield
 */
abstract class data extends persistent {
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
        $this->set($this->datafield(), $value->{$this->datafield()});
    }

    /**
     * Save the value from backup
     *
     * @param $value
     * @throws \moodle_exception
     */
    public function set_rawvalue($value) {
        $this->set($this->datafield(), $value);
    }

    /**
     * Return the value to be used/submitted on form
     *
     * @return mixed
     * @throws \moodle_exception
     */
    public function get_formvalue() {
        return $this->get($this->datafield());
    }

    /**
     * Returns the name of the field to be used on HTML forms.
     *
     * @return string
     * @throws \moodle_exception
     */
    public function inputname(): string {
        return 'customfield_' . $this->get_field()->get('shortname');
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
            return $defaultvalue['text'];
        }
        return $this->raw_get('value');
    }

    /**
     * Must return the name of the field on customfield_data table that is used to store data.
     *
     * @return string field name of customfield_data table used to store data.
     */
    abstract public function datafield(): string;

    /**
     * Add fields on the context edit form.
     *
     * @param \MoodleQuickForm $mform
     */
    abstract public function edit_field_add(\MoodleQuickForm $mform);

    /**
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function edit_save_data(\stdClass $datanew) {
        // TODO: Full refactor of this function.
        global $DB;

        if (!isset($datanew->{$this->inputname()})) {
            // Field not present in form, probably locked and invisible - skip it.
            return false;
        }

        $datarecord = $DB->get_record('customfield_data', ['instanceid' => $datanew->id, 'fieldid' => $this->get_field()->get('id')]);

        if ($datarecord) {
            $this->set('id', $datarecord->id);
        } else {
            $this->set('id', 0);
            $this->set('fieldid', $this->get_field()->get('id'));
            $this->set('instanceid', $datanew->id);
            $this->set('contextid', $datanew->contextid);
            $now = time();
            $this->set('timecreated', $now);
            $this->set('timemodified', $now);
        }
        $datapreprocessed = $this->edit_save_data_preprocess($datanew->{$this->inputname()}, $datanew);
        $this->set($this->datafield(), $datapreprocessed);

        $this->set('value', $datapreprocessed);
        $this->set('valueformat', $this->get_valueformat($this->datafield()));
        $this->save();
        return $this;
    }

    protected function get_valueformat() {
        if (is_int($this->raw_get('valueformat'))) {
            return $this->raw_get('valueformat');
        }

        return FORMAT_HTML;
    }

    /**
     * Hook for child classess to process the data before it gets saved in database
     *
     * @param string|array $data
     * @param \stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     */
    public function edit_save_data_preprocess($data, \stdClass $datarecord) {
        return $data;
    }

    /**
     * Loads an object with data for this field.
     *
     * @param \stdClass $data
     * @throws \moodle_exception
     */
    public function edit_load_data(\stdClass $data) {
        if ($this->get_formvalue() !== null) {
            $data->{$this->inputname()} = $this->get_formvalue();
        }
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
        if ($this->get_field_configdata()['uniquevalues'] == 1) {

            $datafield = $this->datafield();
            $where = "fieldid = ? AND {$datafield} = ?";
            $params = [$this->get_field()->get('id'), $data->{$this->inputname()}];
            if (isset($data->id) && $data->id > 1) {
                $where .= ' AND instanceid != ?';
                $params[] = $data->id;
            }
            if ($DB->record_exists_select('customfield_data', $where, $params)) {
                $errors[$this->inputname()] = get_string('erroruniquevalues', 'core_customfield');
            }
        }
        return $errors;
    }

    /**
     * The configurations of the field as object
     *
     * @return \stdClass
     * @throws \moodle_exception
     */
    public function get_field_configdata() {
        return $this->get_field()->get('configdata');
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
     * Displays the data as html. Used by handler to display data on various places.
     * @return string
     */
    abstract public function display();

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
    public static function load_data(int $id, \stdClass $data, field $field): data {
        $fieldtype      = $field->get('type');
        $customdatatype = "\\customfield_{$fieldtype}\\data";
        if (!class_exists($customdatatype) || !is_subclass_of($customdatatype, data::class)) {
            throw new \moodle_exception(get_string('errordatatypenotfound', 'core_customfield', s($fieldtype)));
        }

        $dataobject = new $customdatatype($id, $data);
        $dataobject->set_field($field);

        return $dataobject;
    }
}
