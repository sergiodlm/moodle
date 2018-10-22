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
 * @package   customfield_text
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_textarea;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package customfield_textarea
 */
class field extends \core_customfield\field {

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_field_to_config_form(\MoodleQuickForm $mform) {
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

    public function before_delete() {
        parent::before_delete();
        // TODO delete all files associated with configdata[defaultvalue]
    }
}
