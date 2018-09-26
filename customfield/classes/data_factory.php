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
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

defined('MOODLE_INTERNAL') || die;

class data_factory {
    const CUSTOMFIELD_TABLE = 'customfield_field';

    public static function load(\stdClass $data, \stdClass $field) : data {
        global $DB;

        if (!$data) {
            $data = $DB->get_record(self::CUSTOMFIELD_TABLE, ['id' => $id]);
        }

        $customfieldtype = "\\customfield_{$field->type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception( get_string('errorfieldtypenotfound', 'core_customfield', s($field->type)) );
        }

        $customdatatype = "\\customfield_{$field->type}\\data";
        if (!class_exists($customdatatype) || !is_subclass_of($customdatatype, data::class)) {
            throw new \coding_exception( get_string('errordatatypenotfound', 'core_customfield', s($field->type)) );
        }

        $data->fieldid = $field->id;
        $categoryname = $data->categoryname;
        unset($data->categoryname);

        $field = new $customfieldtype($field->id, $field);
        $dataobject = new $customdatatype($data->id, $data);
        $dataobject->set_field($field);
        $dataobject->set_data($data);
        $dataobject->set_categoryname($categoryname);

        return $dataobject;
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

}
