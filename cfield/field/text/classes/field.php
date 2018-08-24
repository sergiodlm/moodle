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

namespace cfield_text;

class field extends \core_cfield\field{
    const TYPE = 'text';


    /**
     * Add fields for editing a text field.
     * @param moodleform $mform
     */
    public static function add_fields_edit_form( \MoodleQuickForm $mform) {
        //public static function add_fields_edit_form(\core_cfield\field $fielddefinition, \moodleform $form, \MoodleQuickForm $mform) {

        $mform->addElement('text', 'name', get_string('fieldname', 'core_cfield'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('name'), 'required');

        $mform->addElement('text', 'shortname', get_string('fieldshortname', 'core_cfield'));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', get_string('shortname'), 'required');
    }

    /**
     * Add fields for editing a text profile field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $size = 40;//$this->field->param1;
        $maxlength = 40;//$this->field->param2;
        $fieldtype = 'text'; //($this->field->param3 == 1 ? 'password' : 'text');

        // Create the form field.
        $mform->addElement($fieldtype, $this->shortname, format_string($this->name), 'maxlength="'.$maxlength.'" size="'.$size.'" ');
        $mform->setType($this->shortname, PARAM_TEXT);
    }
}
