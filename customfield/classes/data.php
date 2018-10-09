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
                'recordid'       => [
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
                        'type'     => PARAM_TEXT,
                        'null'     => NULL_NOT_ALLOWED,
                        'default'  => ''
                ],
                // Mandatory field.
                'valueformat'    => [
                        'type'     => PARAM_TEXT,
                        'null'     => NULL_NOT_ALLOWED,
                        'default'  => ''
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
     * @param int $recordid
     * @return data|null
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function load(int $recordid, int $fieldid): self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid, 'recordid' => $recordid]);

        return new self($dbdata->id);
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
            return new self($dbdata->id);
        } else {
            return new self();
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
     * Must return the name of the field on customfield_data table that is used to store data.
     *
     * @return string field name of customfield_data table used to store data.
     */
    public function datafield(): string {
        throw new coding_exception('datafield() method needs to be overridden in each subclass of \core_customfield\data');
    }

    /**
     * Add fields on the context edit form.
     *
     * @param moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {
        throw new coding_exception('edit_field_add() method needs to be overridden in each subclass of \core_customfield\data');
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
        global $DB;

        if (!isset($datanew->{$this->inputname()})) {
            // Field not present in form, probably locked and invisible - skip it.
            return false;
        }

        $datarecord = $DB->get_record(
                'customfield_data',
                ['recordid' => $datanew->id, 'fieldid' => $this->get_field()->get('id')]
        );

        $now = time();
        if ($datarecord) {
            $this->set('id', $datarecord->id);
        } else {
            $this->set('id', 0);
            $this->set('fieldid', $this->get_field()->get('id'));
            $this->set('recordid', $datanew->id);
            $this->set('contextid', $datanew->contextid);
            $this->set('timecreated', $now);
        }
        $this->set($this->datafield(), $this->edit_save_data_preprocess($datanew->{$this->inputname()}, $datanew));
        $this->set('timemodified', $now);

        $this->save();
        return $this;
    }

    /**
     * Hook for child classess to process the data before it gets saved in database
     *
     * @param string $data
     * @param \stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     */
    public function edit_save_data_preprocess(string $data, \stdClass $datarecord) {
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
     * The configurations of the field as object
     *
     * @return \stdClass
     * @throws \moodle_exception
     */
    public function get_field_configdata() {
        // TODO add defaults here.
        return json_decode($this->get_field()->get('configdata'), true);
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
     * @throws \coding_exception
     */
    public function display() {
        throw new coding_exception('display() method needs to be overridden in each subclass of \core_customfield\data');
    }

    /**
     * @param \stdClass $data
     * @param field $field
     * @return data
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function load_data(int $id = 0, \stdClass $data = null, field $field): data {
        $fieldtype      = $field->get('type');
        $customdatatype = "\\customfield_{$fieldtype}\\data";
        if (!class_exists($customdatatype) || !is_subclass_of($customdatatype, data::class)) {
            throw new \moodle_exception(get_string('errordatatypenotfound', 'core_customfield', s($fieldtype)));
        }

        $dataobject = new $customdatatype($id, $data);
        $dataobject->set_field($field);
        if (!is_null($data)) {
            $dataobject->set_formvalue($data);
        }

        return $dataobject;
    }
}
