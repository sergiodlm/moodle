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
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_textarea;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package customfield_select
 */
class data extends \core_customfield\data {
    /**
     * Add fields for editing a textarea field.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {

    }

    /**
     * Process incoming data for the field.
     *
     * @param array|string $data
     * @param \stdClass    $datarecord
     *
     * @return array|mixed|\stdClass|string
     * @throws \coding_exception
     */
    public function edit_save_data_preprocess($fromform, \stdClass $datarecord) {
        if ($fromform['text']) {
            $filearea = $this->get_field()->get('type');
            $context                = \context_course::instance($datarecord->id);
            $textoptions['context'] = $context;
            $textoptions['maxfiles'] = -1;
            $data = (object) ['defaultvalue_editor' => $fromform];
            $data = file_postupdate_standard_editor($data, 'defaultvalue', $textoptions, $context,
                'core_customfield', $filearea, $this->get_field()->get('id'));
            $fromform['text'] = $data->defaultvalue;
        }

        if (is_array($fromform)) {
            $datarecord->dataformat = $fromform['format'];
            $fromform               = $fromform['text'];
        }
        return $fromform;
    }

    /**
     * Load data for this custom field, ready for editing.
     *
     * @param \stdClass $data
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function edit_load_data(\stdClass $data) {
        if (($content = $this->get($this->datafield())) !== null) {
            $context = $this->get_context();
            $data->defaultvalue = $content;
            $data->defaultvalueformat = FORMAT_HTML;
            $fieldid = $this->field->get('id');
            $textoptions = ['context' => $context, 'maxfiles' => -1];
            file_prepare_standard_editor($data, 'defaultvalue', $textoptions, $context, 'core_customfield',
                $this->get_filearea(), $fieldid);
            $content = $data->defaultvalue_editor['text'];
            $this->set('valueformat', FORMAT_HTML);
            $this->set($this->datafield(), clean_text($this->get($this->datafield()), $this->get('valueformat')));
            $data->{api::field_inputname($this->get_field())} = array('text' => $content, 'format' => $this->get('valueformat'));
        }
    }

    public function before_delete() {
        get_file_storage()->delete_area_files($this->get('contextid'), 'core_customfield',
            $this->get_filearea(), $this->field->get('id'));
    }

    /**
     * Get the filearea for the content.
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_filearea() {
        if ($fieldid = $this->get('id')) {
            $filearea = $this->field->get('type');
        } else {
            $filearea = 'defaultvalue_editor';
        }

        return $filearea;
    }
}
