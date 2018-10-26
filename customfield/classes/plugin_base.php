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
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

defined('MOODLE_INTERNAL') || die;

/**
 * Base class for all plugins of type customfield
 *
 * Plugins need to create a class 'plugin' in their namespace that extends this class and override
 * callbacks that they require.
 *
 * @package core_customfield
 */
abstract class plugin_base {

    /**
     * Add fields for editing a text field.
     *
     * @param field $field
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_config_form(field $field, \MoodleQuickForm $mform) {

    }

    /**
     * Return plugin data type.
     *
     * @return string
     */
    public static function datafield() : string {

    }

    /**
     * Display the field data
     * 
     * @param data $data
     * @return string
     */
    public static function display(data $data) {
        global $OUTPUT;
        $type = $data->get_field()->get('type');
        $classpath = "\\customfield_{$type}\\output\\display";
        $obj = new $classpath($data);
        return $OUTPUT->render_from_template("customfield_{$type}/display", $obj);
    }
}
