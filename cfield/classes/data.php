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
 * @copyright 2018, Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_cfield;

defined('MOODLE_INTERNAL') || die;

class data {
    protected $id;
    protected $fieldid;
    protected $recordid;
    protected $intvalue;
    protected $decvalue;
    protected $shortcharval;
    protected $charvalue;
    protected $value;
    protected $valueformat;
    protected $timecreated;
    protected $timemodified;
    protected $contextid;

    private $db;

    const CLASS_TABLE = 'cfield_data';

    public function __construct(\stdClass $data) {
        global $DB;

        $this->id           = !empty($data->id) ? $data->id : null;
        $this->fieldid      = $data->fieldid;
        $this->recordid     = $data->recordid;
        $this->intvalue     = $data->intvalue;
        $this->decvalue     = $data->decvalue;
        $this->shortcharval = $data->shortcharval;
        $this->charvalue    = $data->charvalue;
        $this->value        = $data->value;
        $this->valueformat  = $data->valueformat;
        $this->timecreated  = !empty($data->timecreated) ? $data->timecreated : time();
        $this->timemodified = !empty($data->timemodified) ? $data->timemodified : time();
        $this->contextid    = $data->contextid;

        $this->db = $DB;

        return $this;
    }

    public static function load(int $id) {
        global $DB;

        return new data( $DB->get_record(self::CLASS_TABLE, ['id' => $id]) );
    }

    private function insert() {
        $dataobject = array(
                'fieldid'      => $this->fieldid,
                'recordid'     => $this->recordid,
                'intvalue'     => $this->intvalue,
                'decvalue'     => $this->decvalue,
                'shortcharval' => $this->shortcharval,
                'charvalue'    => $this->charvalue,
                'value'        => $this->value,
                'valueformat'  => $this->valueformat,
                'contextid'    => $this->contextid,
                'timecreated'       => time(),
                'timemodified'      => time(),
        );

        $this->id = $this->db->insert_record($this::CLASS_TABLE, $dataobject, $returnid = true, $bulk = false);
        return $this;
    }

    private function update() {
        $dataobject = array(
                'id'           => $this->id,
                'fieldid'      => $this->fieldid,
                'recordid'     => $this->recordid,
                'intvalue'     => $this->intvalue,
                'decvalue'     => $this->decvalue,
                'shortcharval' => $this->shortcharval,
                'charvalue'    => $this->charvalue,
                'value'        => $this->value,
                'valueformat'  => $this->valueformat,
                'contextid'    => $this->contextid,
                'timecreated'  => $this->timecreated,
                'timemodified' => time(),
        );

        if ($this->db->update_record($this::CLASS_TABLE, $dataobject, $bulk = false)) {
            return $this;
        }
        return false;
    }

    public function save() {
        if (empty($this->id)) {
            return $this->insert();
        }

        return $this->update();
    }

    /**
     * @return null
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param null $id
     * @return data
     */
    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFieldid() {
        return $this->fieldid;
    }

    /**
     * @param mixed $fieldid
     * @return data
     */
    public function setFieldid($fieldid) {
        $this->fieldid = $fieldid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecordid() {
        return $this->recordid;
    }

    /**
     * @param mixed $recordid
     * @return data
     */
    public function setRecordid($recordid) {
        $this->recordid = $recordid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIntvalue() {
        return $this->intvalue;
    }

    /**
     * @param mixed $intvalue
     * @return data
     */
    public function setIntvalue($intvalue) {
        $this->intvalue = $intvalue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDecvalue() {
        return $this->decvalue;
    }

    /**
     * @param mixed $decvalue
     * @return data
     */
    public function setDecvalue($decvalue) {
        $this->decvalue = $decvalue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortcharval() {
        return $this->shortcharval;
    }

    /**
     * @param mixed $shortcharval
     * @return data
     */
    public function setShortcharval($shortcharval) {
        $this->shortcharval = $shortcharval;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCharvalue() {
        return $this->charvalue;
    }

    /**
     * @param mixed $charvalue
     * @return data
     */
    public function setCharvalue($charvalue) {
        $this->charvalue = $charvalue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return data
     */
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValueformat() {
        return $this->valueformat;
    }

    /**
     * @param mixed $valueformat
     * @return data
     */
    public function setValueformat($valueformat) {
        $this->valueformat = $valueformat;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimecreated(): int {
        return $this->timecreated;
    }

    /**
     * @param int $timecreated
     * @return data
     */
    public function setTimecreated(int $timecreated): data {
        $this->timecreated = $timecreated;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimemodified(): int {
        return $this->timemodified;
    }

    /**
     * @param int $timemodified
     * @return data
     */
    public function setTimemodified(int $timemodified): data {
        $this->timemodified = $timemodified;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContextid() {
        return $this->contextid;
    }

    /**
     * @param mixed $contextid
     * @return data
     */
    public function setContextid($contextid) {
        $this->contextid = $contextid;
        return $this;
    }


}
