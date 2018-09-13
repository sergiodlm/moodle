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
use gradereport_singleview\local\screen\select;

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

        return new self($dbdata->id);
    }


    public function id() {
        return $this->get('id');
    }

    public function fieldid(string $value = null) {
        if (! is_null($value)) {
            $this->set('fieldid', $value);
        }
        return $this->get('fieldid');
    }

    public function recordid(string $value = null) {
        if (! is_null($value)) {
            $this->set('recordid', $value);
        }
        return $this->get('recordid');
    }

    public function intvalue(int $value = null) {
        if (! is_null($value)) {
            $this->set('intvalue', $value);
        }
        return $this->get('intvalue');
    }

    public function decvalue(string $value = null) {
        if (! is_null($value)) {
            $this->set('decvalue', $value);
        }
        return $this->get('decvalue');
    }

    public function shortcharvalue(string $value = null) {
        if (! is_null($value)) {
            $this->set('shortcharvalue', $value);
        }
        return $this->get('shortcharvalue');
    }

    public function charvalue(string $value = null) {
        if (! is_null($value)) {
            $this->set('charvalue', $value);
        }
        return $this->get('charvalue');
    }

    public function value(string $value = null) {
        if (! is_null($value)) {
            $this->set('value', $value);
        }
        return $this->get('value');
    }

    public function valueformat(string $value = null) {
        if (! is_null($value)) {
            $this->set('valueformat', $value);
        }
        return $this->get('valueformat');
    }

    public function contextid(string $value = null) {
        if (! is_null($value)) {
            $this->set('contextid', $value);
        }
        return $this->get('contextid');
    }

}


//
//class data {
//    protected $id;
//    protected $fieldid;
//    protected $recordid;
//    protected $intvalue;
//    protected $decvalue;
//    protected $shortcharvalue;
//    protected $charvalue;
//    protected $value;
//    protected $valueformat;
//    protected $timecreated;
//    protected $timemodified;
//    protected $contextid;
//
//    private $db;
//
//    const CLASS_TABLE = 'customfield_data';
//
//    public function __construct(\stdClass $data) {
//        global $DB;
//
//        $this->id           = !empty($data->id) ? $data->id : null;
//        $this->fieldid      = $data->fieldid;
//        $this->recordid     = $data->recordid;
//        $this->intvalue     = $data->intvalue;
//        $this->decvalue     = $data->decvalue;
//        $this->shortcharvalue = $data->shortcharvalue;
//        $this->charvalue    = $data->charvalue;
//        $this->value        = $data->value;
//        $this->valueformat  = $data->valueformat;
//        $this->timecreated  = !empty($data->timecreated) ? $data->timecreated : time();
//        $this->timemodified = !empty($data->timemodified) ? $data->timemodified : time();
//        $this->contextid    = $data->contextid;
//
//        $this->field        = null;
//        $this->category     = null;
//
//        $this->db = $DB;
//
//        return $this;
//    }
//
//    public static function load_recordid_data($component, $area, $recordid) {
//        global $DB;
//
//        $categories = category::list(
//                [
//                        'component' => $component,
//                        'area'      => $area
//                ]
//        );
//
//        $records = $DB->get_records(self::CLASS_TABLE, ['recordid' => $recordid]);
//        $records_array = new \ArrayObject();
//        foreach ($records as $record) {
//            $data = new data($record);
//            foreach ($categories as $category) {
//                foreach ($category->get_fields() as $field) {
//                    if ($field->get_id() == $data->get_fieldid()) {
//                        $data->set_field($field);
//                        $data->set_category($category);
//                    }
//                }
//            }
//            $records_array->append( $data );
//        }
//
//        return $records_array;
//    }
//
//    public static function load(int $id) {
//        global $DB;
//
//        return new data( $DB->get_record(self::CLASS_TABLE, ['id' => $id]) );
//    }
//
//    private function insert() {
//        $dataobject = array(
//                'fieldid'      => $this->fieldid,
//                'recordid'     => $this->recordid,
//                'intvalue'     => $this->intvalue,
//                'decvalue'     => $this->decvalue,
//                'shortcharvalue' => $this->shortcharvalue,
//                'charvalue'    => $this->charvalue,
//                'value'        => $this->value,
//                'valueformat'  => $this->valueformat,
//                'contextid'    => $this->contextid,
//                'timecreated'       => time(),
//                'timemodified'      => time(),
//        );
//
//        $this->id = $this->db->insert_record($this::CLASS_TABLE, $dataobject, $returnid = true, $bulk = false);
//        return $this;
//    }
//
//    private function update() {
//        $dataobject = array(
//                'id'           => $this->id,
//                'fieldid'      => $this->fieldid,
//                'recordid'     => $this->recordid,
//                'intvalue'     => $this->intvalue,
//                'decvalue'     => $this->decvalue,
//                'shortcharvalue' => $this->shortcharvalue,
//                'charvalue'    => $this->charvalue,
//                'value'        => $this->value,
//                'valueformat'  => $this->valueformat,
//                'contextid'    => $this->contextid,
//                'timecreated'  => $this->timecreated,
//                'timemodified' => time(),
//        );
//
//        if ($this->db->update_record($this::CLASS_TABLE, $dataobject, $bulk = false)) {
//            return $this;
//        }
//        return false;
//    }
//
//    public function save() {
//        if (empty($this->id)) {
//            return $this->insert();
//        }
//
//        return $this->update();
//    }
//
//    /**
//     * @return null
//     */
//    public function get_id() {
//        return $this->id;
//    }
//
//
//    /**
//     * @return mixed
//     */
//    public function get_fieldid() {
//        return $this->fieldid;
//    }
//
//    /**
//     * @param mixed $fieldid
//     * @return data
//     */
//    public function set_fieldid($fieldid) {
//        $this->fieldid = $fieldid;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_recordid() {
//        return $this->recordid;
//    }
//
//    /**
//     * @param mixed $recordid
//     * @return data
//     */
//    public function set_recordid($recordid) {
//        $this->recordid = $recordid;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_intvalue() {
//        return $this->intvalue;
//    }
//
//    /**
//     * @param mixed $intvalue
//     * @return data
//     */
//    public function set_intvalue($intvalue) {
//        $this->intvalue = $intvalue;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_decvalue() {
//        return $this->decvalue;
//    }
//
//    /**
//     * @param mixed $decvalue
//     * @return data
//     */
//    public function set_decvalue($decvalue) {
//        $this->decvalue = $decvalue;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_shortcharvalue() {
//        return $this->shortcharvalue;
//    }
//
//    /**
//     * @param mixed $shortcharvalue
//     * @return data
//     */
//    public function set_shortcharvalue($shortcharvalue) {
//        $this->shortcharvauell = $shortcharvalue;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_charvalue() {
//        return $this->charvalue;
//    }
//
//    /**
//     * @param mixed $charvalue
//     * @return data
//     */
//    public function set_charvalue($charvalue) {
//        $this->charvalue = $charvalue;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_value() {
//        return $this->value;
//    }
//
//    /**
//     * @param mixed $value
//     * @return data
//     */
//    public function set_value($value) {
//        $this->value = $value;
//        return $this;
//    }
//
//    /**
//     * @return mixed
//     */
//    public function get_valueformat() {
//        return $this->valueformat;
//    }
//
//    /**
//     * @param mixed $valueformat
//     * @return data
//     */
//    public function set_valueformat($valueformat) {
//        $this->valueformat = $valueformat;
//        return $this;
//    }
//
//    /**
//     * @return int
//     */
//    public function get_timecreated(){
//        return $this->timecreated;
//    }
//
//    /**
//     * @return int
//     */
//    public function get_timemodified(){
//        return $this->timemodified;
//    }
//
//
//    /**
//     * @return mixed
//     */
//    public function get_contextid() {
//        return $this->contextid;
//    }
//
//    /**
//     * @param mixed $contextid
//     * @return data
//     */
//    public function set_contextid($contextid) {
//        $this->contextid = $contextid;
//        return $this;
//    }
//
//    /**
//     * @param field $field
//     * @return data
//     */
//    public function set_field(field $field) {
//    $this->field = $field;
//    return $this;
//}
//
//    /**
//     * @return field
//     */
//    public function get_field() {
//        return $this->field;
//    }
//
//    /**
//     * @param category $category
//     * @return data
//     */
//    public function set_category(category $category) {
//        $this->category = $category;
//        return $this;
//    }
//
//    /**
//     * @return category
//     */
//    public function get_category() {
//        return $this->category;
//    }
//
//    public static function bulk_delete_from_fields(array $fieldids) {
//        global $DB;
//
//        if (!empty($fieldids)) {
//            $where = 'fieldid<0';
//            foreach ($fieldids as $fieldid) {
//                $where .= " OR fieldid=$fieldid";
//            }
//
//            if (! $DB->delete_records_select(self::CLASS_TABLE, $where)) {
//                return false;
//            }
//        }
//
//        return true;
//    }
//
//}
