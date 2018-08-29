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
 * @package   cfield_text
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace cfield_textarea;

class field extends \core_cfield\field {

    const TYPE = 'textarea';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     * @param moodleform $mform
     */
    public static function add_field_to_edit_form( \MoodleQuickForm $mform) {
        //public static function add_fields_edit_form(\core_cfield\field $fielddefinition, \moodleform $form, \MoodleQuickForm $mform) {
global $PAGE;
        $desceditoroptions = array(
                'trusttext' => true,
                'subdirs' => true,
                'maxfiles' => 5,
                'maxbytes' => 0,
                'context' => $PAGE->context,
                'noclean' => 0,
                'enable_filemanagement' => true);

        $mform->addElement('editor', 'textarea_editor', get_string('description', 'core_cfield'), null, $desceditoroptions);
        $mform->setType('textarea_editor', PARAM_RAW);
    }

    /**
     * Add fields for editing a textarea field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $mform->addElement('editor', $this->dataobject->shortname, format_string($this->dataobject->name));
        $mform->setType($this->dataobject->shortname, PARAM_TEXT);
    }

    public function set_data($data) {
        $this->data = $data->charvalue;
    }
}
