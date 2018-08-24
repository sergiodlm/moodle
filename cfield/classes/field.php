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

use Horde\Socket\Client\Exception;

defined('MOODLE_INTERNAL') || die;

abstract class field {
    protected $id;
    protected $shortname;
    protected $name;
    protected $type;
    protected $description;
    protected $descriptionformat;
    protected $sortorder;
    protected $categoryid;
    protected $configdata;
    protected $timecreated;
    protected $timemodified;

    private $db;
    const CLASS_TABLE = 'cfield_field';
    const LENGTH_SHORTNAME = 100;
    const LENGTH_NAME = 400;
    const LENGTH_TYPE = 100;

    public function __construct(\stdClass $fielddata) {
        global $DB;

        if (
                empty($fielddata->shortname) ||
                empty($fielddata->name) ||
                empty($fielddata->type) ||
                empty($fielddata->categoryid)
        ) {
            throw new Exception();
        }

        $this->id                = !empty($fielddata->id) ? $fielddata->id : null;
        $this->shortname         = $fielddata->shortname;
        $this->name              = $fielddata->name;
        $this->type              = $fielddata->type;
        $this->description       = !empty($fielddata->description) ? $fielddata->description : null;
        $this->descriptionformat = !empty($fielddata->descriptionformat) ? $fielddata->descriptionformat : null;
        $this->sortorder         = !empty($fielddata->sortorder) ? $fielddata->sortorder : null;
        $this->categoryid        = $fielddata->categoryid;
        $this->configdata        = !empty($fielddata->configdata) ? $fielddata->configdata : null;
        $this->timecreated       = !empty($fielddata->timecreated) ? $fielddata->timecreated : time();
        $this->timemodified      = !empty($fielddata->timemodified) ? $fielddata->timemodified : time();

        $this->db = $DB;

        return $this;
    }

    public function delete() {
        $this->db->delete_records($this::CLASS_TABLE, ['id' => $this->id]);
        return true;
    }

    private function insert() {
        $dataobject = array(
                'shortname'         => $this->shortname,
                'name'              => $this->name,
                'type'              => $this->type,
                'description'       => $this->description,
                'descriptionformat' => $this->descriptionformat,
                'sortorder'         => $this->sortorder,
                'categoryid'        => $this->categoryid,
                'configdata'        => $this->configdata,
                'timecreated'       => time(),
                'timemodified'      => time(),
        );

        $this->id = $this->db->insert_record($this::CLASS_TABLE, $dataobject, $returnid = true, $bulk = false);
        return $this;
    }

    private function update() {
        $dataobject = array(
                'id'                => $this->id,
                'shortname'         => $this->shortname,
                'name'              => $this->name,
                'type'              => $this->type,
                'description'       => $this->description,
                'descriptionformat' => $this->descriptionformat,
                'sortorder'         => $this->sortorder,
                'categoryid'        => $this->categoryid,
                'configdata'        => $this->configdata,
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

    public static function return_fields_from_categories($categories_list) {
        global $DB;

        if (empty($categories_list)) {
            return array();
        }

        list($sql, $params) = $DB->get_in_or_equal($categories_list, SQL_PARAMS_NAMED, 'id', true, false);
        $where = 'WHERE categoryid '.$sql;

        $fields_array = new \ArrayObject();
        foreach ($DB->get_records_sql('SELECT * FROM {'.self::CLASS_TABLE.'} '.$where, $params) as $field) {
            $fields_array->append(field_factory::load($field->id));
        }

        return $fields_array;
    }

    /**
     * @return mixed
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return field
     */
    public function set_id($id) {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_shortname() {
        return $this->shortname;
    }

    /**
     * @param mixed $shortname
     * @return field
     */
    public function set_shortname($shortname) {
        $this->shortname = $shortname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return field
     */
    public function set_name($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return field
     */
    public function set_type($type) {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return field
     */
    public function set_description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_descriptionformat() {
        return $this->descriptionformat;
    }

    /**
     * @param mixed $descriptionformat
     * @return field
     */
    public function set_descriptionformat($descriptionformat) {
        $this->descriptionformat = $descriptionformat;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_sortorder() {
        return $this->sortorder;
    }

    /**
     * @param mixed $sortorder
     * @return field
     */
    public function set_sortorder($sortorder) {
        $this->sortorder = $sortorder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_categoryid() {
        return $this->categoryid;
    }

    /**
     * @param mixed $categoryid
     * @return field
     */
    public function set_categoryid($categoryid) {
        $this->categoryid = $categoryid;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_configdata() {
        return $this->configdata;
    }

    /**
     * @param mixed $configdata
     * @return field
     */
    public function set_configdata($configdata) {
        $this->configdata = $configdata;
        return $this;
    }

    /**
     * @return mixed
     */
    public function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     * @return field
     */
    public function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
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

}