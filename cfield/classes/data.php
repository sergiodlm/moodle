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

                'timecreated'       => time(),
                'timemodified'      => time(),
        );

        $this->id = $this->db->insert_record($this::CLASS_TABLE, $dataobject, $returnid = true, $bulk = false);
        return $this;
    }

    private function update() {
        $dataobject = array(
                'id'                => $this->id,

                'timecreated'       => $this->timecreated,
                'timemodified'      => time(),
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

}
