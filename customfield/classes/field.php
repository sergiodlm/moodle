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
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use core\persistent;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package core_customfield
 */
abstract class field extends persistent {
    /**
     * Database table.
     */
    const TABLE = 'customfield_field';

    /**
     * Add field parameters to the field configuration form
     *
     * @param \MoodleQuickForm $mform
     */
    abstract public function add_field_to_edit_form( \MoodleQuickForm $mform);

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'shortname' => [
                        'type' => PARAM_TEXT,
                ],
                'name' => [
                        'type' => PARAM_TEXT,
                ],
                'type' => [
                        'type' => PARAM_TEXT,
                ],
                'description' => [
                        'type' => PARAM_RAW,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'descriptionformat' => [
                        'type' => PARAM_INT,
                        'default' => FORMAT_MOODLE,
                        'optional' => true
                ],
                'sortorder' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'required' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'locked' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'uniquevalues' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'visibility' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'categoryid' => [
                        'type' => PARAM_INT
                ],
                'configdata' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
        );
    }

    /**
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     * @throws \coding_exception
     * @throws \dml_write_exception
     */
    protected function validate_shortname($value) {
        if ( strpos($value, ' ') !== false ) {
            throw new \dml_write_exception(get_string('invalidshortnameerror', 'core_customfield'));
        }

        return true;
    }

    /**
     * @return data|null
     * @throws \coding_exception
     */
    public function data(): data {
        return data::fieldload($this->get('id'));
    }


    // Get total count of fields for this category.

    /**
     * @param int $categoryid
     * @return int
     * @throws \dml_exception
     */
    private static function count_fields(int $categoryid)  {
        global $DB;

        return $DB->count_records(
                self::TABLE,
                [
                        'categoryid' => $categoryid
                ]
        );
    }

    /**
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_count_fields() : int {
        return $this::count_fields( $this->get('categoryid') );
    }

    /**
     * @return bool
     * @throws \coding_exception
     */
    protected function before_delete() : bool {
        if ( $this->data()->get('id') > 0 ) {
            $this->data()->delete();
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_create() : bool {
        return $this::reorder();
    }

    /**
     * @param bool $result
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) :bool  {
        return $this->reorder();
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function static_reorder($categoryid): bool {
        global $DB;

        $fieldneighbours = $DB->get_records(
                self::TABLE,
                [
                        'categoryid' => $categoryid
                ],
                'sortorder DESC');

        $neworder = count($fieldneighbours);

        foreach ($fieldneighbours as $field) {
            $dataobject = new \stdClass();
            $dataobject->id = $field->id;
            $dataobject->sortorder = --$neworder;
            if (!$DB->update_record(self::TABLE, $dataobject)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function reorder() : bool {
        return $this::static_reorder($this->get('categoryid'));
    }

    /**
     * @param int $position
     * @return field
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function move(int $position): self {
        global $DB;

        $nextfielddata = $DB->get_record(
                $this::TABLE,
                [
                        'sortorder' => $this->get('sortorder') + $position,
                        'categoryid' => $this->get('categoryid')
                ]
        );

        if (!empty($nextfielddata)) {
            $previusfield = field_factory::load($nextfielddata->id);
            $previusfield->set('sortorder', $this->get('sortorder'));
            $previusfield->save();
            $this->set('sortorder', $this->get('sortorder') + $position);
            $this->save();
        }

        return $this;
    }

    /**
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function up(): self {
        return $this->move(1);
    }

    /**
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function down(): self {
        return $this->move(-1);
    }

    public function drag_and_drop($from, $to, $newcategoryid = null) {
        if ($from < 1 || $to < 1 || $newcategoryid < 1) {
            return false;
        }

        //TODO: refactoring of this pending
        if (!is_null($newcategoryid)) {
            $newcategory = new category($newcategoryid);
            $oldcategory = new category($this->get('categoryid'));

            $this->set('categoryid', $newcategory->get('id'));
            $this->save();

            $oldcategory->reorder();
            $oldcategory->save();

            $this->set('sortorder', 0);
            $newcategory->reorder();
            $newcategory->save();

            //Always after change category:
            $from = 0;
        }

        //in case of $from == $to
        $return = null;
        if ($from < $to) {
            for ($i = $from; $i < $to; $i++) {
                $return = $this->up();
            }
        } else if ($to > $from) {
            for ($i = $to; $i < $from; $i++) {
                $return = $this->down();
            }
        }

        return $return;
    }

    /**
     * Tweaks the edit form.
     * @param moodleform $mform instance of the moodleform class
     * @return bool
     */
    public function edit_after_data($mform) {
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
     * @param moodleform $mform instance of the moodleform class
     * @throws \coding_exception
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname())) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/course:update', context_course::instance($this->get('courseid')))) {
            $mform->hardFreeze($this->inputname());
            $mform->setConstant($this->inputname(), $this->data());
        }
    }

    /**
     * Loads an object with data for this field.
     *
     * @param stdClass $user a user object
     * @throws \coding_exception
     */
    public function edit_load_data($data) {
        if ($this->data() !== null) {
           $data->{$this->inputname()} = $this->data();
        }
    }

    public function set_datarecord($datavalues) {
        $data = data::load($datavalues->recordid, $datavalues->fieldid);

        $data->intvalue($datavalues->intvalue);
        $data->decvalue($datavalues->decvalue);
        $data->shortcharvalue($datavalues->shortcharvalue);
        $data->charvalue($datavalues->charvalue);
        $data->value($datavalues->value);
        $data->valueformat($datavalues->valueformat);
        $data->contextid($datavalues->contextid);
        $data->save();
    }

    /**
     * Saves the data coming from form
     *
     * @param stdClass $datanew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function edit_save_data($datanew) {
        global $DB;

        // TODO: handle unchecked checkboxes.
	if (!isset($datanew->{$this->inputname()})) {
	    // Field not present in form, probably locked and invisible - skip it.
	    return;
	}

	$datarecord = $DB->get_record('customfield_data', array('recordid' => $datanew->id, 'fieldid' => $this->get('id')));

	$datanew->{$this->inputname()} = $this->edit_save_data_preprocess($datanew->{$this->inputname()}, $datanew);

	if ($datarecord) {
            $datarecord->{$this->datafield()} = $datanew->{$this->inputname()};
	    $datarecord->timemodified = time();
	    $result = $DB->update_record('customfield_data', $datarecord);
	} else {
	    $now = time();
            $datarecord = new \stdclass();
            $datarecord->{$this->datafield()} = $datanew->{$this->inputname()};
	    $datarecord->fieldid = $this->get('id');
            $datarecord->recordid = $datanew->id;
	    $datarecord->timecreated = $now;
	    $datarecord->timemodified = $now;
	    $result = $DB->insert_record('customfield_data', $datarecord);
	}
	return $result;
    }

    /**
     * Hook for child classess to process the data before it gets saved in database
     * @param stdClass $data
     * @param stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return $data;
    }

    public function should_display() {
        // TODO: text config/attribute;
        return true;
    }

    public function inputname() {
        return 'customfield_'.$this->shortname();
    }

}
