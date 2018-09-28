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

defined('MOODLE_INTERNAL') || die;

/**
 * Class field_factory
 *
 * @package core_customfield
 */
class field_factory {

    const CUSTOMFIELD_TABLE = 'customfield_field';

    /**
     * Returns a correct class field.
     *
     * @param int $id
     * @param \stdClass|null $field
     * @return field
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function load(int $id, \stdClass $field = null) : field {
        global $DB;

        if (!$field || empty($field->type)) {
            $field = $DB->get_record(self::CUSTOMFIELD_TABLE, ['id' => $id]);
        }

        $customfieldtype = "\\customfield_{$field->type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception( get_string('errorfieldtypenotfound', 'core_customfield', s($field->type)) );
        }

        return new $customfieldtype($field->id, $field);
    }

    /**
     * @param string $type
     * @return field
     * @throws \coding_exception
     */
    public static function create(string $type) : field {

        $customfieldtype = "\\customfield_{$type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception( get_string('errorfieldtypenotfound', 'core_customfield', s($type)) );
        }

        $field = new $customfieldtype();
        $field->set('type', $type);
        return $field;
    }

    /**
     * @param int $categoryid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_fields_from_category_array(int $categoryid) :array {
        global $DB;

        $fields = array();
        $records = $DB->get_records(self::CUSTOMFIELD_TABLE, ['categoryid' => $categoryid], 'sortorder DESC');
        foreach ($records as $fielddata) {
            $fields[] = self::load($fielddata->id);
        }
        return $fields;
    }

    /**
     * @param int $from
     * @param int $to
     * @param int $category
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function drag_and_drop(int $from, int $to, int $category) : bool {
        $fieldfrom = self::load($from);

        if ( $fieldfrom->get('categoryid') != $category ) {
            $oldcategory = $fieldfrom->get('categoryid');

            $fieldfrom->set('categoryid', $category);
            $fieldfrom->set('sortorder', -1);
            $fieldfrom->save();

            category::reorder_fields($oldcategory);
            category::reorder_fields($fieldfrom->get('categoryid'));
        }

        if ( $to > 0 ) {
            $fieldto   = self::load($to);
            if ($fieldfrom->get('sortorder') < $fieldto->get('sortorder')) {
                for ($i = $fieldfrom->get('sortorder'); $i < $fieldto->get('sortorder'); $i++) {
                    $fieldfrom->up();
                }
            } else if ($fieldfrom->get('sortorder') > $fieldto->get('sortorder')) {
                for ($i = $fieldfrom->get('sortorder'); $i > $fieldto->get('sortorder')+1; $i--) {
                    $fieldfrom->down();
                }
            }
        } else {
            for ($i = $fieldfrom->get('sortorder'); $i > 0; $i--) {
                $fieldfrom->down();
            }
        }

        return true;
    }
}
