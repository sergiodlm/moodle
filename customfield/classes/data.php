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
     * @var core_customfield\field
     */
    protected $field;

    /**
     * Name of the category that the related field belongs to (used on forms and webservices).
     *
     * @var string
     */
    protected $categoryname;

    /**
     * Data from the form.
     *
     * @var string
     */
    protected $data;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'fieldid' => [
                        'type' => PARAM_TEXT,
                ],
                'recordid' => [
                        'type' => PARAM_TEXT,
                ],
                'intvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'decvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'charvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'shortcharvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'value' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'valueformat' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'contextid' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ]
        );
    }

    /**
     * @param int $fieldid
     * @param int $recordid
     * @return data|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function load(int $recordid, int $fieldid) : self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid, 'recordid' => $recordid]);

        return new self($dbdata->id);
    }

    public static function fieldload(int $fieldid) : self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid]);

        if ($dbdata) {
            return new self($dbdata->id);
        } else {
            return new self();
        }
    }

    public function set_field($field) {
        $this->field = $field;
    }

    public function get_field() {
        return $this->field;
    }

    public function set_categoryname($name) {
        $this->categoryname = $name;
    }

    public function get_categoryname() {
        return $this->categoryname;
    }

    public function set_data($data) {
        $this->data = $data;
    }

    public function get_data() {
        return $this->data;
    }

    public function inputname() {
        return 'customfield_' . $this->get_field()->get('shortname');
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
            return;
        }

        $datarecord = $DB->get_record('customfield_data', array('recordid' => $datanew->id, 'fieldid' => $this->get_field()->get('id')));

        $datanew->{$this->inputname()} = $this->edit_save_data_preprocess($datanew->{$this->inputname()}, $datanew);


        // TODO: Refactor to use this->set and persistent stuff.
        // TODO: get and save contextid;
        if ($datarecord) {
            $datarecord->{$this->datafield()} = $datanew->{$this->inputname()};
            $datarecord->timemodified         = time();
            $result                           = $DB->update_record('customfield_data', $datarecord);
        } else {
            $now                              = time();
            $datarecord                       = new \stdclass();
            $datarecord->{$this->datafield()} = $datanew->{$this->inputname()};
            $datarecord->fieldid              = $this->get('id');
            $datarecord->recordid             = $datanew->id;
            $datarecord->timecreated          = $now;
            $datarecord->timemodified         = $now;
            $result                           = $DB->insert_record('customfield_data', $datarecord);
        }
        return $result;
    }

    /**
     * Hook for child classess to process the data before it gets saved in database
     *
     * @param \stdClass $data
     * @param \stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     * @return int
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function edit_save_data_preprocess(string $data, \stdClass $datarecord) {
        return $data;
    }

    /**
     * Loads an object with data for this field.
     *
     * @param \stdClass $data
     * @throws \coding_exception
     */
    public function edit_load_data(\stdClass $data) {
        if ($this->get_data() !== null) {
            $data->{$this->inputname()} = $this->get_data();
        }
    }

    public function get_field_configdata() {
        return json_decode($this->get_field()->get('configdata'));
    }

    public function should_display() {
        $visibility = $this->get_field()->get('visibility');
        if ($visibility == 0) {
            return false;
        } else if ($visibility == 1) {
            return true; //has_capability('moodle/course:update', \context::instance_by_id($this->get('contextid')));
        } else {
            return true;
        }
    }

    /**
     * Tweaks the edit form.
     *
     * @param \MoodleQuickForm $mform
     * @return bool
     * @throws \moodle_exception
     */
    public function edit_after_data(\MoodleQuickForm $mform) {
        if (!$this->is_editable()) {
            return false;
        }
        $this->edit_field_set_locked($mform);
        return true;
    }

    /**
     * TODO: check capabilities.
     *
     * @return field
     */
    public function is_editable() {
        return true;
    }

    /**
     * TODO: check locked status.
     *
     * @return field
     */
    public function is_locked() {
        return false;
    }

    /**
     * HardFreeze the field if locked.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function edit_field_set_locked(\MoodleQuickForm $mform) {
        if (!$mform->elementExists($this->inputname())) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/course:update', context_course::instance($this->get('courseid')))) {
            $mform->hardFreeze($this->inputname());
            $mform->setConstant($this->inputname(), $this->get_data());
        }
    }
}
