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
    protected $fields;

    private $db;
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

        $this->id                = empty($categorydata->id) ? null : $categorydata->id;
        $this->name              = $categorydata->name;
        $this->description       = empty($categorydata->description) ? null : $categorydata->description;
        $this->descriptionformat = empty($categorydata->descriptionformat) ? null : $categorydata->descriptionformat;
        $this->sortorder         = empty($categorydata->sortorder) ? null : $categorydata->sortorder;
        $this->timecreated       = empty($categorydata->timecreated) ? null : time();
        $this->timemodified      = empty($categorydata->timemodified) ? null : time();
        $this->component         = $categorydata->component;
        $this->area              = $categorydata->area;
        $this->itemid            = empty($categorydata->itemid) ? null : $categorydata->itemid;
        $this->contextid         = empty($categorydata->contextid) ? null : $categorydata->contextid;
        $this->fields            = new \ArrayObject();

        $this->db = $DB;

        return $this;
    }

    private function reorder(): bool {
        $categoryneighbours = $this->db->get_records(
                $this::CLASS_TABLE,
                [
                        'area'      => $this->area,
                        'itemid'    => $this->itemid,
                        'contextid' => $this->contextid
                ]
        );

        $neworder = count($categoryneighbours);
        foreach ($categoryneighbours as $category) {
            $dataobject            = new \stdClass();
            $dataobject->id        = $category->id;
            $dataobject->sortorder = $neworder--;
            if (!$this->db->update_record($this::CLASS_TABLE, $dataobject)) {
                return false;
            }
        }

        return true;
    }

    public function up() : self {
        $previuscategorydata = $this->db->get_record(
                $this::CLASS_TABLE,
                [
                        'sortorder' => $this->get_sortorder() + 1,
                        'area'      => $this->area,
                        'itemid'    => $this->itemid,
                        'contextid' => $this->contextid
                ]
        );

        if (!empty($previuscategorydata)) {
            $previuscategory = new category($previuscategorydata);
            $previuscategory->set_sortorder( $this->get_sortorder() - 1 );
            $previuscategory->save();
            $this->set_sortorder( $this->get_sortorder() + 1 );
            $this->save();
        }

        return $this;
    }

    public function down() : self {
        $nextcategorydata = $this->db->get_record(
                $this::CLASS_TABLE,
                [
                        'sortorder' => $this->get_sortorder() - 1,
                        'area'      => $this->area,
                        'itemid'    => $this->itemid,
                        'contextid' => $this->contextid
                ]
        );

        if (!empty($nextcategorydata)) {
            $nextcategory = new category($nextcategorydata);
            $nextcategory->set_sortorder( $this->get_sortorder() + 1 );
            $nextcategory->save();
            $this->set_sortorder( $this->get_sortorder() - 1 );
            $this->save();
        }

        return $this;
    }

    public function delete() {
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

        return $this->db->delete_records(self::CLASS_TABLE, ['id' => $this->id]);
    }

    private function insert() {
        $dataobject = array(
                'name'              => $this->name,
                'description'       => $this->description,
                'descriptionformat' => $this->descriptionformat,
                'sortorder'         => $this->sortorder,
                'timecreated'       => time(),
                'timemodified'      => time(),
                'component'         => $this->component,
                'area'              => $this->area,
                'itemid'            => $this->itemid,
                'contextid'         => $this->contextid
        );
        $this->id   = $this->db->insert_record(self::CLASS_TABLE, $dataobject, $returnid = true, $bulk = false);

        foreach ($this->fields as $field) {
            $field->set_categoryid($this->id);
            $field->save();
        }

        return $this;
    }

    private function update() {
        $dataobject = array(
                'id'                => $this->id,
                'name'              => $this->name,
                'description'       => $this->description,
                'descriptionformat' => $this->descriptionformat,
                'sortorder'         => $this->sortorder,
                'timecreated'       => $this->timecreated,
                'timemodified'      => time(),
                'component'         => $this->component,
                'area'              => $this->area,
                'itemid'            => $this->itemid,
                'contextid'         => $this->contextid
        );

        foreach ($this->fields as $field) {
            $field->set_categoryid($this->id);
            $field->save();
        }

        if ($this->db->update_record(self::CLASS_TABLE, $dataobject, $bulk = false)) {
            return $this;
        }
        return false;
    }

    public function save() {
        if (empty($this->id)) {
            $this->reorder();
            return $this->insert();
        }

        return $this->update();
    }

    public function get_id() {
        return $this->id;
    }

    public function get_name() {
        return $this->name;
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
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return category
     */
    public
    function set_description($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_descriptionformat() {
        return $this->descriptionformat;
    }

    /**
     * @param mixed $descriptionformat
     * @return category
     */
    public
    function set_descriptionformat($descriptionformat) {
        $this->descriptionformat = $descriptionformat;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_sortorder() {
        return $this->sortorder;
    }

    /**
     * @param mixed $sortorder
     * @return category
     */
    public
    function set_sortorder($sortorder) {
        $this->sortorder = $sortorder;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_timecreated() {
        return $this->timecreated;
    }

    /**
     * @param mixed $timecreated
     * @return category
     */
    public
    function set_timecreated($timecreated) {
        $this->timecreated = $timecreated;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_timemodified() {
        return $this->timemodified;
    }

    /**
     * @param mixed $timemodified
     * @return category
     */
    public
    function set_timemodified($timemodified) {
        $this->timemodified = $timemodified;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_component() {
        return $this->component;
    }

    /**
     * @param mixed $component
     * @return category
     */
    public
    function set_component($component) {
        $this->component = $component;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_area() {
        return $this->area;
    }

    /**
     * @param mixed $area
     * @return category
     */
    public
    function set_area($area) {
        $this->area = $area;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_itemid() {
        return $this->itemid;
    }

    /**
     * @param mixed $itemid
     * @return category
     */
    public
    function set_itemid($itemid) {
        $this->itemid = $itemid;
        return $this;
    }

    /**
     * @return mixed
     */
    public
    function get_contextid() {
        return $this->contextid;
    }

    /**
     * @param mixed $contextid
     * @return category
     */
    public
    function set_contextid($contextid) {
        $this->contextid = $contextid;
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
        $this->name = $name;
        return $this;
    }
}
