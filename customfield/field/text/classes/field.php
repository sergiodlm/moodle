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

namespace customfield_text;

class field extends \core_customfield\field {

    const TYPE = 'text';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     * @param moodleform $mform
     */
    public static function add_field_to_edit_form( \MoodleQuickForm $mform) {
        //public static function add_fields_edit_form(\core_customfield\field $fielddefinition, \moodleform $form, \MoodleQuickForm $mform) {

        $linkstargetlist = array(
                ''          => get_string('none', 'core_customfield'),
                '_blank'    => get_string('newwindow', 'core_customfield'),
                '_self'     => get_string('sameframe', 'core_customfield'),
                '_top'      => get_string('samewindow', 'core_customfield')
        );

        // Max length.
        $mform->addElement('text', 'configdata[maxlength]', get_string('maxlength', 'core_customfield'), ['size' => self::SIZE]);
        $mform->setType('configdata[maxlength]', PARAM_INT);

        // Link.
        $mform->addElement('text', 'configdata[link]', get_string('link', 'core_customfield'), ['size' => self::SIZE]);
        $mform->setType('configdata[link]', PARAM_URL);

        // Link target.
        $mform->addElement('select', 'configdata[linktarget]', get_string('linktarget', 'core_customfield'), $linkstargetlist);
    }

    /**
     * Add fields for editing a text profile field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $shortname = 'customfield_'.$this->shortname();
        $mform->addElement(self::TYPE, $shortname, format_string($this->get('name')), 'size="'.self::SIZE.'" ');
        $mform->setType($shortname, PARAM_TEXT);
    }

    public function set_data($data) {
        $this->data = $data->charvalue;
    }

    public function datafield() {
        return 'charvalue';
    }

    public function display() {
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->name()), ['class' => 'customfieldname customfieldtext']).
               ' : '.
               \html_writer::tag('span', format_string($this->data), ['class' => 'customfieldvalue customfieldtext']).
               \html_writer::end_tag('div');
    }
}
