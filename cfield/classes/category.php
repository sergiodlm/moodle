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
 * @copyright 2018, David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_cfield;

use core_calendar\local\event\proxies\std_proxy;
use Horde\Socket\Client\Exception;

defined('MOODLE_INTERNAL') || die;

class category {
    protected $dataobject;
    /*
    protected $id;
    protected $name;
    protected $description;
    protected $descriptionformat;
    protected $sortorder;
    protected $timecreated;
    protected $timemodified;
    protected $component;
    protected $area;
    protected $itemid;
    protected $contextid;
    */
    protected $fields;

    const CLASS_TABLE = 'cfield_category';
    const LENGTH_NAME = 400;
    const LENGTH_COMPONENT = 100;
    const LENGTH_AREA = 100;

    public function __construct(\stdClass $categorydata) {
        global $DB;

        if (
                empty($categorydata->name)      ||
                empty($categorydata->area)      ||
                empty($categorydata->component)
        ) {
            throw new Exception();
        }

        $this->dataobject = $categorydata;
        $this->fields = new \ArrayObject();

        return $this;
    }

    public function delete() {
        global $DB;

        $category = category::load($this->get_id());

        if ( count($category->get_fields()) > 0 ) {
            $fields_array = array();
            foreach ($category->get_fields() as $field) {
                $fields_array[] = $field->get_id();
            }
            if (! field_factory::bulk_delete($fields_array)) {
                return false;
            }
        }

        return $DB->delete_records(self::CLASS_TABLE, ['id' => $this->id]);
    }

    private function insert() {
        global $DB;

        $now = time();
        $this->dataobject->timecreated = $now;
        $this->dataobject->timemodified = $now;
        $this->id = $DB->insert_record(self::CLASS_TABLE, $this->dataobject);

        foreach ($this->fields as $field) {
            $field->set_categoryid($this->id);
            $field->save();
        }

        return $this;
    }

    private function update() {
        global $DB;

        $this->dataobject->timemodified = $now;

        foreach ($this->fields as $field) {
            $field->set_categoryid($this->id);
            $field->save();
        }

        if ($DB->update_record(self::CLASS_TABLE, $this->dataobject)) {
            return $this;
        }
        return false;
    }

    public function save() {
        if (empty($this->dataobject->id)) {
            return $this->insert();
        }

        return $this->update();
    }

    public function get_id() {
        return $this->dataobject->id;
    }

    public function get_name() {
        return $this->dataobject->name;
    }

    public function set_fields($field) {
        $this->fields->append($field);
        return $this->fields;
    }

    public static function list(array $options) {
        global $DB;

        return $DB->get_records(self::CLASS_TABLE, $options, 'sortorder DESC');
    }

    public static function load(int $id) {
        return category::load_array(['id' => $id])[0];
    }

    public static function simple_load(int $id) {
        global $DB;

        return new category( $DB->get_record(self::CLASS_TABLE, ['id' => $id]) );
    }

    public static function load_array(array $options) {
        $categories = self::list($options);

        $categories_array = new \ArrayObject();
        $categories_list  = array();
        foreach ($categories as $category) {
            $categories_list[] = $category->id;
        }

        $fields = field::return_fields_from_categories($categories_list);

        foreach ($categories as $category) {
            $categoryobject = new category($category);

            foreach ($fields as $field) {
                if ($field->get_categoryid() == $categoryobject->get_id()) {
                    $categoryobject->set_fields(field_factory::load($field->get_id()));
                }
            }
            $categories_array->append($categoryobject);
        }

        return $categories_array;
    }

    public function has_fields() {
        return !empty($this->fields);
    }

    /**
     * @return mixed
     */
    public
    function get_description() {
        return $this->dataobject->description;
    }

    /**
     * @param mixed $description
     * @return category
     */
    public
    function set_description($description) {
        $this->dataobject->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_descriptionformat() {
        return $this->dataobject->descriptionformat;
    }

    /**
     * @param mixed $descriptionformat
     * @return category
     */
    public
    function set_descriptionformat($descriptionformat) {
        $this->dataobject->descriptionformat = $descriptionformat;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_sortorder() {
        return $this->dataobject->sortorder;
    }

    /**
     * @param mixed $sortorder
     * @return category
     */
    public
    function set_sortorder($sortorder) {
        $this->dataobject->sortorder = $sortorder;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_timecreated() {
        return $this->dataobject->timecreated;
    }

    /**
     * @param mixed $timecreated
     * @return category
     */
    public
    function set_timecreated($timecreated) {
        $this->dataobject->timecreated = $timecreated;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_timemodified() {
        return $this->dataobject->timemodified;
    }

    /**
     * @param mixed $timemodified
     * @return category
     */
    public
    function set_timemodified($timemodified) {
        $this->dataobject->timemodified = $timemodified;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_component() {
        return $this->dataobject->component;
    }

    /**
     * @param mixed $component
     * @return category
     */
    public
    function set_component($component) {
        $this->dataobject->component = $component;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_area() {
        return $this->dataobject->area;
    }

    /**
     * @param mixed $area
     * @return category
     */
    public
    function set_area($area) {
        $this->dataobject->area = $area;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_itemid() {
        return $this->dataobject->itemid;
    }

    /**
     * @param mixed $itemid
     * @return category
     */
    public
    function set_itemid($itemid) {
        $this->dataobject->itemid = $itemid;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_contextid() {
        return $this->dataobject->contextid;
    }

    /**
     * @param mixed $contextid
     * @return category
     */
    public
    function set_contextid($contextid) {
        $this->dataobject->contextid = $contextid;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_fields() {
        return $this->fields;
    }

    /**
     * @param mixed $name
     * @return category
     */
    public
    function set_name($name) {
        $this->dataobject->name = $name;
        return $this;
    }
}
