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
 * @package   customfield_textarea
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_textarea;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\data;
use core_customfield\plugin_base;

/**
 * Class data
 *
 * @package customfield_textarea
 */
class plugin extends plugin_base {

    const SIZE = 20;
    const DATATYPE = 'value';

    /**
     * Add fields for editing a textarea field.
     *
     * @param \core_customfield\field $field
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_config_form(\core_customfield\field $field, \MoodleQuickForm $mform) {
        global $PAGE;
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_textarea'));
        $mform->setExpanded('header_specificsettings', true);

        $desceditoroptions = array(
                'trusttext'             => true,
                'subdirs'               => true,
                'maxfiles'              => -1,
                'maxbytes'              => 0,
                'context'               => $PAGE->context,
                'noclean'               => 0,
                'enable_filemanagement' => true);

        $mform->addElement('editor', 'configdata[defaultvalue]', get_string('defaultvalue', 'core_customfield'), null, $desceditoroptions);
        $mform->setType('configdata[defaultvalue]', PARAM_RAW);
    }

    // TODO: move to a trait.
    /**
     * @return string
     */
    public static function datafield() :string {
        return self::DATATYPE;
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public static function edit_field_add(\core_customfield\field $field, \MoodleQuickForm $mform) {
        global $PAGE;
        $desceditoroptions = array(
                'trusttext'             => true,
                'subdirs'               => true,
                'maxfiles'              => -1,
                'maxbytes'              => 0,
                'context'               => $PAGE->context,
                'noclean'               => 0,
                'enable_filemanagement' => true);
        $mform->addElement('editor', api::field_inputname($field), format_string($field->get('name')), null, $desceditoroptions);
        $mform->setType( api::field_inputname($field), PARAM_RAW);
    }

}