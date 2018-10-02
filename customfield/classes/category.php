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
                        'default' => -1,
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
        $categoryneighbours = self::list($options);

        $neworder = count($categoryneighbours);

        foreach ($categoryneighbours as $category) {
            $category->set('sortorder', --$neworder);
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
     * Updates sort order after create
     *
     * @return bool
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function after_create() : bool {
        return $this->reorder();
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
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) :bool {
        return $this->reorder();
    }

    /**
     * Total number of categories
     *
     * @return int
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_count_categories(): int {
        global $DB;
        return $DB->count_records('customfield_category',
                [
                        'component' => $this->get('component'),
                        'area' => $this->get('area'),
                        'itemid' => $this->get('itemid')
                ]
        );
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
     * Move the category one position up
     *
     * @return category
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function up(): self {
        return $this->move(1);
    }

    /**
     * Mode a category one position down
     *
     * @return category
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function down(): self {
        return $this->move(-1);
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

        foreach ($DB->get_records(self::TABLE, $options, 'sortorder DESC') as $categorydata) {
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
        $categoryfrom = new self($from);
        $categoryto   = new self($to);

        if ($categoryfrom->get('sortorder') < $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i < $categoryto->get('sortorder'); $i++) {
                $categoryfrom->up();
            }
        } else if ($categoryfrom->get('sortorder') > $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i > $categoryto->get('sortorder') + 1; $i--) {
                $categoryfrom->down();
            }
        }

        return true;
    }

    /**
     * Update sort order of the fields
     *
     * @param int $categoryid
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function reorder_fields(int $categoryid): bool {
        global $DB;

        $fieldneighbours = $DB->get_records(field::TABLE, ['categoryid' => $categoryid], 'sortorder DESC');

        $neworder = count($fieldneighbours);

        foreach ($fieldneighbours as $field) {
            $dataobject            = new \stdClass();
            $dataobject->id        = $field->id;
            $dataobject->sortorder = --$neworder;
            if (!$DB->update_record(field::TABLE, $dataobject)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Set category name
     *
     * @param string $name
     * @throws \moodle_exception
     */
    protected function set_name($name) {
        global $DB;
        $name = trim($name);
        if ($name === '') {
            throw new \moodle_exception('fieldrequired', 'core_customfield');
        }
        $select = 'component = :component AND area = :area AND itemid = :itemid AND name = :name';
        if ($this->get('id')) {
            $select .= ' AND id <> :id';
        }
        if ($DB->record_exists_select(self::TABLE, $select, ['name' => $name] + (array)$this->to_record())) {
            throw new \moodle_exception('formcategorycheckname', 'core_customfield');
        }
        $this->raw_set('name', $name);
    }

    /**
     * Returns an object for inplace editable
     *
     * @param bool $editable
     * @return inplace_editable
     */
    public function get_inplace_editable(bool $editable = true) : inplace_editable {
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
