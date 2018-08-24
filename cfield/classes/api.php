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

class api {

    //Returns array of categories, each of them contains a list of fields definitions
    public static function get_fields_definitions($component, $area, $itemid) {
        global $DB;

        $options = [
                'component' => $component,
                'area' => $area,
                'itemid' => $itemid
        ];

        return category::load_array( $options );
    }

    //public static function insert_field($data) {
    //    global $DB;
    //
    //    $insertid = $DB->insert_record('cfield_field', (object) [
    //            'shortname' => $data->shortname,
    //            'name' => $data->name,
    //            'type' => $data->type,
    //            'description' => $data->description,
    //            'descriptionformat' => $data->descriptionformat,
    //            'sortorder' => $data->descriptionformat,
    //            'categoryid' => $data->categoryid,
    //            'configdata' => $data->configdata,
    //            'timecreated' => time(),
    //            'timemodified' => time()
    //    ], true);
    //
    //    return $insertid;
    //}
    //
    //public static function update_field($data) {
    //    global $DB;
    //
    //    $updatearray = [
    //            'id' => $data->id,
    //            'name' => $data->name,
    //            'shortname' => $data->shortname,
    //            'timemodified' => time()
    //    ];
    //
    //    $DB->update_record('cfield_field', $updatearray);
    //}

    public static function get_field($id) {
        return field_factory::load($id);
    }

}