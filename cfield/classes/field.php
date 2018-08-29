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

abstract class field {
    protected $dataobject;
    protected $data;

    private $db;

    const CLASS_TABLE = 'cfield_field';
    const LENGTH_SHORTNAME = 100;
    const LENGTH_NAME = 400;
    const LENGTH_TYPE = 100;

    public function __construct(\stdClass $fielddata) {
        $this->dataobject = $fielddata;
        return $this;
    }

    private static function reorder($categoryid): bool {
        global $DB;

        $fieldneighbours = $DB->get_records( self::CLASS_TABLE, [ 'categoryid' => $categoryid ], 'sortorder DESC' );

        $neworder = count($fieldneighbours);
        foreach ($fieldneighbours as $field) {
            $dataobject            = new \stdClass();
            $dataobject->id        = $field->id;
            $dataobject->sortorder = $neworder--;
            if (!$DB->update_record(self::CLASS_TABLE, $dataobject)) {
                return false;
            }
        }

        return true;
    }

    public function up() : self {
        $previusfielddata = $this->db->get_record(
                $this::CLASS_TABLE,
                [
                        'sortorder'  => $this->get_sortorder() + 1,
                        'categoryid' => $this->get_categoryid()
                ]
        );

        if (!empty($previusfielddata)) {
            $previusfield = new field_factory($previusfielddata);
            $previusfield->set_sortorder( $this->get_sortorder() - 1 );
            $previusfield->save();
            $this->set_sortorder( $this->get_sortorder() + 1 );
            $this->save();
        }

        return $this;
    }

    public function down() : self {
        $previusfielddata = $this->db->get_record(
                $this::CLASS_TABLE,
                [
                        'sortorder'  => $this->get_sortorder() - 1,
                        'categoryid' => $this->get_categoryid()
                ]
        );

        if (!empty($previusfielddata)) {
            $previusfield = new field_factory($previusfielddata);
            $previusfield->set_sortorder( $this->get_sortorder() + 1 );
            $previusfield->save();
            $this->set_sortorder( $this->get_sortorder() - 1 );
            $this->save();
        }

        return $this;
    }


    public function delete() {
        global $DB;

        if (!data::bulk_delete_from_fields([$this->get_id()])) {
            return false;
        }
        return $DB->delete_records($this::CLASS_TABLE, ['id' => $this->get_id()]);
    }

    private function insert() {
        global $DB;

        $now = time();
        $this->dataobject->timecreated = $now;
        $this->dataobject->timemodified = $now;
        $this->dataobject->sortorder = 0;

        $this->set_id($DB->insert_record($this::CLASS_TABLE, $this->dataobject));
        return $this;
    }

    private function update() {
        global $DB;

        $this->set_timemodified(time());
        if ($DB->update_record($this::CLASS_TABLE, $this->dataobject)) {
            return $this;
        }
        return false;
    }

    public function save() {
        if (!$this->get_id()) {
            self::reorder( $this->get_categoryid() );
            return $this->insert();
        }

        return $this->update();
    }

    public static function return_fields_from_categories($categories_list) {
        global $DB;

        if (empty($categories_list)) {
            return array();
        }

        list($sql, $params) = $DB->get_in_or_equal($categories_list, SQL_PARAMS_NAMED, 'id', true, false);
        $where = 'WHERE categoryid '.$sql;

        $fields_array = new \ArrayObject();
        foreach ($DB->get_records_sql('SELECT * FROM {'.self::CLASS_TABLE.'} '.$where.' ORDER BY sortorder DESC', $params) as $field) {
            $fields_array->append(field_factory::load($field->id));
        }

        return $fields_array;
    }

    /**
     * @return mixed
     */
    public function get_id() {
        if (isset($this->dataobject->id)) {
            return $this->dataobject->id;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $id
     * @return field
     */
    public function set_id($id) {
        $this->dataobject->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_shortname() {
        return $this->dataobject->shortname;
    }

    /**
     * @param mixed $shortname
     * @return field
     */
    public function set_shortname($shortname) {
        $this->dataobject->shortname = $shortname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_name() {
        return $this->dataobject->name;
    }

    /**
     * @param mixed $name
     * @return field
     */
    public function set_name($name) {
        $this->dataobject->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_type() {
        return $this->dataobject->type;
    }

    /**
     * @param mixed $type
     * @return field
     */
    public function set_type($type) {
        $this->dataobject->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_description() {
        return $this->dataobject->description;
    }

    /**
     * @param mixed $description
     * @return field
     */
    public function set_description($description) {
        $this->dataobject->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_descriptionformat() {
        return $this->dataobject->descriptionformat;
    }

    /**
     * @param mixed $descriptionformat
     * @return field
     */
    public function set_descriptionformat($descriptionformat) {
        $this->dataobject->descriptionformat = $descriptionformat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_sortorder() {
        return $this->dataobject->sortorder;
    }

    /**
     * @param mixed $sortorder
     * @return field
     */
    public function set_sortorder($sortorder) {
        $this->dataobject->sortorder = $sortorder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_categoryid() {
        return $this->dataobject->categoryid;
    }

    /**
     * @param mixed $categoryid
     * @return field
     */
    public function set_categoryid($categoryid) {
        $this->dataobject->categoryid = $categoryid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_configdata() {
        return $this->dataobject->configdata;
    }

    /**
     * @param mixed $configdata
     * @return field
     */
    public function set_configdata($configdata) {
        $this->dataobject->configdata = $configdata;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_timecreated() {
        return $this->dataobject->timecreated;
    }

    /**
     * @param mixed $timecreated
     * @return field
     */
    public function set_timecreated($timecreated) {
        $this->dataobject->timecreated = $timecreated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     * @return field
     */
    public function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
        return $this;
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
     * Hook for child classess to process the data before it gets saved in database
     * @param stdClass $data
     * @param stdClass $datarecord The object that will be used to save the record
     * @return  mixed
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return $data;
    }

    /**
     * Print out the form field.
     * @param moodleform $mform instance of the moodleform class
     * @return bool
     */
    public function edit_field($mform) {
        if (!$this->is_editable()) {
            return false;
        }

        $this->edit_field_add($mform);
        //$this->edit_field_set_default($mform);
        $this->edit_field_set_required($mform);
        $this->edit_field_set_maxlength($mform);

        return true;
    }

    /**
     * Sets the default data for the field in the form object
     * @param  moodleform $mform instance of the moodleform class
     */
    //public function edit_field_set_default($mform) {
    //    if (!empty($this->field->defaultdata)) {
    //        $mform->setDefault($this->shortname, $this->field->defaultdata);
    //    }
    //}

    public function edit_field_set_maxlength($mform) {
        $maxlength = $this->has_maxlength();
        if ($maxlength && ($this->is_editable())) {
            $mform->addRule($this->shortname, get_string('maxlength', 'core_cfield'), 'maxlength', $maxlength, 'client');
        }
    }

    public function has_maxlength() {
        $configdata = json_decode( $this->get_configdata() );
        if(isset($configdata->maxlength)) {
            return $configdata->maxlength;
        }
        return false;
    }

     /**
     * Sets the required flag for the field in the form object
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_required($mform) {
        if ($this->is_required() && ($this->is_editable())) {
            $mform->addRule($this->shortname, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * HardFreeze the field if locked.
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->shortname)) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/course:update', context_course::instance($this->courseid))) {
            $mform->hardFreeze($this->shortname);
            $mform->setConstant($this->shortname, $this->data);
        }
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
     * Check if the field is required.
     * @internal This method should not generally be overwritten by child classes.
     * @return bool
     */
    public function is_required() {
        $configdata = json_decode( $this->get_configdata() );
        if($configdata) {
            return (boolean)$configdata->required;
        }
        return false;
    }

    public function set_datarecord($data) {
        $this->datarecord = $data;
        $this->set_data($data);
    }

    public function set_data($data) {
        $this->data = $data;
    }

    /**
     * Saves the data coming from form
     * @param stdClass $datanew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($datanew) {
        global $DB;

        if (!isset($datanew->{$this->shortname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        $data = new \stdClass();

        $datanew->{$this->shortname} = $this->edit_save_data_preprocess($datanew->{$this->shortname}, $data);

        $this->datarecord->charvalue = $datanew->{$this->shortname};

        if ($this->datarecord->id) {
            $this->set_timemodified(time());
            $DB->update_record('cfield_data', $this->datarecord);
        } else {
            $now = time();
            $this->set_timecreated($now);
            $this->set_timemodified($now);
            $DB->insert_record('cfield_data', $this->datarecord);
        }
    }

    /**
     * Validate the custom field.
     *
     * @param stdClass $datanew data coming from the form
     * @return  array contains error messages
     */
    public function edit_validate_field($datanew) {
        return [];
    }

    /**
     * Loads an object with data for this field.
     * @param stdClass $user a user object
     */
    public function edit_load_data($data) {
        if ($this->data !== null) {
            $data->{$this->shortname} = $this->data;
        }
    }
}
