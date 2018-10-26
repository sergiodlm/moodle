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

use core_customfield\api;
use core_customfield\handler;

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
     * Saves the data coming from form
     *
     * @param \stdClass $datanew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function edit_save_data(\stdClass $datanew) {
        $fromform = $datanew->{api::field_inputname($this->get_field()).'_editor'};

        if (!$this->get('id')) {
            $this->set('value', '');
            $this->set('valueformat', FORMAT_MOODLE);
            $this->save();
        }

        if ($fromform['text']) {
            $textoptions = plugin::value_editor_options($this->get_field(), $this);
            $data = (object) ['field_editor' => $fromform];
            $data = file_postupdate_standard_editor($data, 'field', $textoptions, $textoptions['context'],
                'customfield_textarea', 'value', $this->get('id'));
            $this->set('value', $data->field);
            $this->set('valueformat', $data->fieldformat);
            $this->save();
        }
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
        if ($this->get('id')) {
            $text = $this->get('value');
            $format = $this->get('valueformat');
            $temp = (object)['field' => $text, 'fieldformat' => $format];
            $textoptions = plugin::value_editor_options($this->get_field(), $this);
            file_prepare_standard_editor($temp, 'field', $textoptions, $textoptions['context'], 'customfield_textarea',
                'value', $this->get('id'));
            $value = $temp->field_editor;
        } else {
            $text = $this->get_field()->get_configdata_property('defaultvalue');
            $format = $this->get_field()->get_configdata_property('defaultvalueformat');
            $temp = (object)['field' => $text, 'fieldformat' => $format];
            $textoptions = plugin::value_editor_options($this->get_field());
            file_prepare_standard_editor($temp, 'field', $textoptions, $textoptions['context'], 'customfield_textarea',
                'defaultvalue', $this->get_field()->get('id'));
            $value = $temp->field_editor;
        }
        $data->{api::field_inputname($this->get_field()).'_editor'} = $value;
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
