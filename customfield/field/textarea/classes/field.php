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

class field extends \core_customfield\field {

    const TYPE = 'textarea';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     * @param moodleform $mform
     */
    public static function add_field_to_edit_form( \MoodleQuickForm $mform) {
        global $PAGE;
        $desceditoroptions = array(
                'trusttext' => true,
                'subdirs' => true,
                'maxfiles' => 5,
                'maxbytes' => 0,
                'context' => $PAGE->context,
                'noclean' => 0,
                'enable_filemanagement' => true);

        $mform->addElement('editor', 'textarea_editor', get_string('description', 'core_customfield'), null, $desceditoroptions);
        $mform->setType('textarea_editor', PARAM_RAW);
    }

    /**
     * Add fields for editing a textarea field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $shortname = 'customfield_'.$this->get('shortname');
        $mform->addElement('editor', $shortname, format_string($this->get('name')));
        $mform->setType($shortname, PARAM_TEXT);
        $mform->setDefault($shortname, $this->data);
    }

    public function display() {
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->get_name()), ['class' => 'customfieldname']).
               \html_writer::tag('span', format_text($this->get_data()), ['class' => 'customfieldvalue']).
               \html_writer::end_tag('div');
    }

    public function set_data($data) {
        $this->data = $data->value;
    }

    public function datafield() {
        return 'value';
    }

    /**
     * Process incoming data for the field.
     * @param stdClass $data
     * @param stdClass $datarecord
     * @return mixed|stdClass
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        if (is_array($data)) {
            $datarecord->dataformat = $data['format'];
            $data = $data['text'];
        }
        return $data;
    }
}
