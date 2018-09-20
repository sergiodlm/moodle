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

/**
 * Class field
 *
 * @package customfield_textarea
 */
class field extends \core_customfield\field {

    const TYPE = 'textarea';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_edit_form(\MoodleQuickForm $mform) {
        global $PAGE;
        $desceditoroptions = array(
                'trusttext'             => true,
                'subdirs'               => true,
                'maxfiles'              => 5,
                'maxbytes'              => 0,
                'context'               => $PAGE->context,
                'noclean'               => 0,
                'enable_filemanagement' => true);

        $mform->addElement('editor', 'textarea_editor', get_string('description', 'core_customfield'), null, $desceditoroptions);
        $mform->setType('textarea_editor', PARAM_RAW);
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\moodleform $mform) {
        $mform->addElement('editor', $this->inputname(), format_string($this->get('name')));
        $mform->setType($this->inputname(), PARAM_RAW);
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function display() {
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->name()), ['class' => 'customfieldname']) .
               \html_writer::tag('span', format_text($this->get('data')), ['class' => 'customfieldvalue']) .
               \html_writer::end_tag('div');
    }

    /**
     * @param $data
     * @throws \coding_exception
     */
    public function set_data($data) {
        $this->set('data' ,$data->value);
    }

    /**
     * @return string
     */
    public function datafield() :string  {
        return 'value';
    }

    /**
     * Process incoming data for the field.
     *
     * @param \stdClass $data
     * @param \stdClass $datarecord
     * @return mixed|\stdClass
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        if (is_array($data)) {
            $datarecord->dataformat = $data['format'];
            $data                   = $data['text'];
        }
        return $data;
    }

    /**
     * Load data for this custom field, ready for editing.
     *
     * @param $data
     * @throws \coding_exception
     */
    public function edit_load_data($data) {
        if ($this->get('data') !== null) {
            $this->set('dataformat', 1);
            $this->set('data', clean_text($this->get('data'), $this->get('dataformat')));
            $data->{$this->get('inputname')} = array('text' => $this->get('data'), 'format' => $this->get('dataformat'));
        }
    }
}
