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
     * @return array|null
     * @throws \coding_exception
     */
    public function fields() {
        return field_factory::get_fields_from_category_array($this->get('id'));
    }

    /**
     * @param $options
     * @return bool
     * @throws \coding_exception
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
     * @return bool
     * @throws \coding_exception
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
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_create() : bool {
        return $this->reorder();
    }

    /**
     * @param $result
     * @return bool
     * @throws \coding_exception
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
     * @param bool $result
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) :bool {
        return $this->reorder();
    }

    /**
     * @return int
     * @throws \dml_exception
     * @throws \coding_exception
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
     * @param int $position
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function move(int $position): self {
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
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function up(): self {
        return $this->move(1);
    }

    /**
     * @return category
     * @throws \coding_exception
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
     * @throws \coding_exception
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
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function drag_and_drop_block($from, $to) {
        $categoryfrom = new self($from);
        $categoryto   = new self($to);

        //TODO: refactoting this
        if ($categoryfrom->get('sortorder') < $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i < $categoryto->get('sortorder'); $i++) {
                $categoryfrom->up();
            }
        } elseif ($categoryfrom->get('sortorder') > $categoryto->get('sortorder')) {
            for ($i = $categoryfrom->get('sortorder'); $i > $categoryto->get('sortorder')+1; $i--) {
                $categoryfrom->down();
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function reorder_fields($categoryid): bool {
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

}



