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

use core\output\inplace_editable;
use core\persistent;

defined('MOODLE_INTERNAL') || die;

/**
 * Class category
 *
 * @package core_customfield
 */
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
    protected static function define_properties(): array {
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
                        'default' => 0
                ],
                'contextid' => [
                        'type' => PARAM_INT,
                        'optional' => false
                ],
                'sortorder' => [
                        'type' => PARAM_INT,
                        'optional' => false,
                        'default' => -1
                ],
        );
    }

    /**
     * @return field[]
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function fields() {
        return field::get_fields_from_category_array($this->get('id'));
    }

    /**
     * Updates sortorder (used on drag and drop)
     *
     * @param $options
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected static function static_reorder($options): bool {
        // TODO move to api and rename to reorder_categories($component, $area, $itemid)
        $categoryneighbours = self::list($options);

        // First let's move the new element at the end of categories list.
        $lastcategorysortorder = 0;
        foreach ($categoryneighbours as $category) {
            if ($category->get('sortorder') > $lastcategorysortorder) {
                $lastcategorysortorder = $category->get('sortorder');
            }
        }
        foreach ($categoryneighbours as $category) {
            if ($category->get('sortorder') < 0) {
                $category->set('sortorder', $lastcategorysortorder+1);
            }
        }

        // And now let's update sortorder values in the database.
        $sortfunction = function(category $a, category $b): int {
            return $a->get('sortorder') <=> $b->get('sortorder');
        };

        usort($categoryneighbours, $sortfunction);

        foreach ($categoryneighbours as $sortorder => $category) {
            $category->set('sortorder', $sortorder);
            $category->save();
        }

        return true;
    }

    /**
     * Calls static_reorder()
     *
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function reorder(): bool {
        // TODO should be protected. Unit test should use api::reorder_categories
        $this::static_reorder(
                [
                        'component' => $this->get('component'),
                        'area' => $this->get('area'),
                        'itemid' => $this->get('itemid')
                ]
        );

        return true;
    }

    /**
     * Hook to execute after an update.
     *
     * @param bool $result Whether or not the update was successful.
     * @return void
     */
    protected function after_update($result) {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
    }

    /**
     * Updates sort order after create
     *
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function after_create() {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
        $this->reorder();
    }

    /**
     * Delete fields before delete category
     *
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function before_delete() : bool {
        foreach ($this->fields() as $field) {
            if (!$field->delete()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Update sort order after delete
     *
     * @param bool $result
     * @return void
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
        $this->reorder();
    }

    /**
     * Move a category (used by drag and drop)
     *
     * @param int $position
     * @return category
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    private function move(int $position) : self {
        $nextcategory = self::list(
                [
                        'sortorder' => $this->get('sortorder') + $position,
                        'component' => $this->get('component'),
                        'area' => $this->get('area'),
                        'itemid' => $this->get('itemid'),
                ]
        )[0];

        if (!empty($nextcategory)) {
            $nextcategory->set('sortorder', $this->get('sortorder'));
            $nextcategory->save();
            $this->set('sortorder', $this->get('sortorder') + $position);
            $this->save();
        }
        return $this;
    }

    /**
     * Returns a list of categories with their related fields.
     *
     * @param array $options
     * @return category[]
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function list(array $options): array {
        global $DB;

        $categories = array();

        foreach ($DB->get_records(self::TABLE, $options, 'sortorder') as $categorydata) {
            $categories[] = new self(0, $categorydata);
        }

        return $categories;
    }

    /**
     * Backend function for Drag and Drop
     *
     * @param $from
     * @param $to
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function drag_and_drop_block(int $from, int $to) : bool {
        // TODO move to api::move_category(category $category, int $beforeid = 0)
        $categoryfrom = new self($from);
        $categoryto   = new self($to);

        // Move to the last position on the list.
        if ($to === 0) {
            $categoryfrom->set('sortorder', -1);
            $categoryfrom->save();
            $output = $categoryfrom->reorder();
            return $output;
        }

        if ($categoryfrom->get('sortorder') < $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i < $categoryto->get('sortorder')-1; $i++) {
                $categoryfrom->move(1);
            }
        } else if ($categoryfrom->get('sortorder') > $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i > $categoryto->get('sortorder'); $i--) {
                $categoryfrom->move(-1);
            }
        }

        return true;
    }

    /**
     * Returns an object for inplace editable
     *
     * @param bool $editable
     * @return inplace_editable
     * @throws \coding_exception
     */
    public function get_inplace_editable(bool $editable = true) : inplace_editable {
        // TODO move to api::get_category_inplace_editable(category $category, bool $editable = true)
        return new inplace_editable('core_customfield',
            'category',
            $this->get('id'),
            $editable,
            format_string($this->get('name')),
            $this->get('name'),
            get_string('editcategoryname', 'core_customfield'),
            get_string('newvaluefor', 'core_form', format_string($this->get('name')))
        );
    }
}
