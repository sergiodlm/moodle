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
 * @copyright 2018, David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use core\persistent;
use stdClass;
use ArrayObject;

defined('MOODLE_INTERNAL') || die;

class category extends persistent {
    /**
     * Database table.
     */
    const TABLE = 'customfield_category';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
                'name' => [
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
                'component' => [
                        'type' => PARAM_TEXT
                ],
                'area' => [
                        'type' => PARAM_TEXT
                ],
                'itemid' => [
                        'type' => PARAM_INT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'contextid' => [
                        'type' => PARAM_INT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'sortorder' => [
                        'type' => PARAM_INT,
                        'default' => -1,
                ],
        );
    }

    /**
     * @return int
     * @throws \coding_exception
     */
    public function id() {
        return $this->get('id');
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function name() {
        return $this->get('name');
    }

    public function sortorder($value = null): int {
        if (!is_null($value)) {
            $this->set('sortorder', $value);
        }
        return $this->get('sortorder');
    }

    public function fields() {
        return $this->fields;
    }

    private static function static_reorder($options): bool {
        $categoryneighbours = self::load_array($options);

        $neworder = count($categoryneighbours);

        foreach ($categoryneighbours as $category) {
            $category->sortorder(--$neworder);
            $category->save();
        }

        return true;
    }

    protected function reorder() {
        $this::static_reorder(
                [
                        'component' => $this->get('component'),
                        'area' => $this->get('area'),
                        'itemid' => $this->get('itemid')
                ]
        );

        return true;
    }

    protected function after_create() {
        return $this->reorder();
    }

    protected function after_delete($result) {
        return $this->reorder();
    }

    /**
     * @return int
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public function get_count_categories() {
        global $DB;
        return $DB->count_records('customfield_category',
                [
                        'component' => $this->get('component'),
                        'area' => $this->get('area')
                ]
        );
    }

    /**
     * Returns a list of categories.
     *
     * @param array $options
     * @return array
     * @throws \dml_exception
     */
    public static function list(array $options) {
        global $DB;

        return $DB->get_records(self::TABLE, $options, 'sortorder DESC');
    }

    /**
     * Returns a list of categories with their related fields.
     *
     * @param array $options
     * @return category[]
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function load_array(array $options) {
        $categories = self::list($options);

        $categories_array = array();
        $categories_list = array();
        foreach ($categories as $category) {
            $categories_list[] = $category->id;
        }

        $fields = field::return_fields_from_categories($categories_list);

        foreach ($categories as $category) {
            $categoryobject = new category(0, $category);

            foreach ($fields as $field) {
                $categoryobject->fields = array();
                if ($field->get_categoryid() == $categoryobject->get('id')) {
                    $categoryobject->fields[] = field_factory::load($field->get_id());
                }
            }
            $categories_array[] = $categoryobject;
        }

        return $categories_array;
    }

    public static function load(int $id) {
        return new category($id);
    }

    private function move(int $position): self {
        $previuscategory = self::load_array(
                [
                        'sortorder' => $this->get('sortorder') + $position,
                        'component' => $this->get('component'),
                        'area' => $this->get('area'),
                        'itemid' => $this->get('itemid'),
                ]
        )[0];

        if (!empty($previuscategory)) {
            $previuscategory->sortorder($this->get('sortorder'));
            $previuscategory->save();
            $this->sortorder($this->get('sortorder') + $position);
            $this->save();
        }

        return $this;
    }

    public function up(): self {
        return $this->move(1);
    }

    public function down(): self {
        return $this->move(-1);
    }

}



