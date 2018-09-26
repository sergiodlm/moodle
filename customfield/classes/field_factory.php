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

use Horde\Socket\Client\Exception;

defined('MOODLE_INTERNAL') || die;

class field_factory {

    const CUSTOMFIELD_TABLE = 'customfield_field';

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

    public static function create(string $type) : field {

        $customfieldtype = "\\customfield_{$type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception( get_string('errorfieldtypenotfound', 'core_customfield', s($type)) );
        }

        $field = new $customfieldtype();
        $field->set('type', $type);
        return $field;
    }

    public static function get_fiedls_from_category_array(int $categoryid) :array {
        global $DB;

        $fields = array();
        foreach ( $DB->get_records(
                self::CUSTOMFIELD_TABLE,
                [
                        'categoryid' => $categoryid
                ],
                'sortorder DESC'
        ) as $fielddata) {
            $fields[] = self::load($fielddata->id);
        }

        return $fields;
    }

    public static function bulk_delete(array $ids) {
        global $DB;

        if (!empty($ids)) {
            if (!data::bulk_delete_from_fields($ids)) {
                return false;
            }

            $where = 'id<0';
            foreach ($ids as $id) {
                $where .= " OR id=$id";
            }

            if (! $DB->delete_records_select(self::CUSTOMFIELD_TABLE, $where)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $from
     * @param $to
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function drag_and_drop($from, $to) {
        $fieldfrom = self::load($from);
        $fieldto   = self::load($to);

        //TODO: refactoting this
        if ($fieldfrom->get('sortorder') < $fieldto->get('sortorder')) {
            for ($i = $fieldfrom->get('sortorder'); $i < $fieldto->get('sortorder'); $i++) {
                $fieldfrom->up();
            }
        } elseif ($fieldfrom->get('sortorder') > $fieldto->get('sortorder')) {
            for ($i = $fieldfrom->get('sortorder'); $i > $fieldto->get('sortorder'); $i--) {
                $fieldfrom->down();
            }
        }

        return true;
    }

}
