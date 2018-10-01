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

/**
 * Class data_factory
 *
 * @package core_customfield
 */
class data_factory {

    /**
     * @param \stdClass $data
     * @param field $field
     * @return data
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public static function load(int $id = 0, \stdClass $data, field $field) : data {
        $fieldtype = $field->get('type');
        $customdatatype = "\\customfield_{$fieldtype}\\data";
        if (!class_exists($customdatatype) || !is_subclass_of($customdatatype, data::class)) {
            throw new \moodle_exception(get_string('errordatatypenotfound', 'core_customfield', s($fieldtype)));
        }

        $dataobject = new $customdatatype($id, $data);
        $dataobject->set_field($field);
        $dataobject->set_formvalue($data);

        return $dataobject;
    }
}
