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
 * @package   customfield_checkbox
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_checkbox;

defined('MOODLE_INTERNAL') || die;

use core_customfield\plugin_base;

/**
 * Class data
 *
 * @package customfield_checkbox
 */
class plugin extends plugin_base {

    /**
     * Add fields for editing a checkbox field.
     *
     * @param field $field
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_config_form(\core_customfield\field $field, \MoodleQuickForm $mform) {
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_checkbox'));
        $mform->setExpanded('header_specificsettings', true);

        $mform->addElement('selectyesno', 'configdata[checkbydefault]', get_string('checkedbydefault', 'customfield_checkbox'));
        $mform->setType('configdata[checkbydefault]', PARAM_BOOL);
    }
}
