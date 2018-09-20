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
 * @package   customfield_date
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_date;

/**
 * Class field
 *
 * @package customfield_date
 */
class field extends \core_customfield\field{
    const TYPE = 'date';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_field_to_edit_form( \MoodleQuickForm $mform) {
        $mform->addElement('checkbox', 'configdata[dateincludetime]', get_string('includetime', 'core_customfield'));
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\moodleform $mform) {
        // Get the current calendar in use - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        // Check if the field is required.
        $config = json_decode($this->configdata());
        $optional = ($config->required != 1);

        $attributes = ['optional' => $optional];

        if (!empty($config->dateincludetime)) {
            $mform->addElement('date_time_selector', $this->inputname(), format_string($this->name()), $attributes);
        } else {
            $mform->addElement('date_selector', $this->inputname(), format_string($this->name()), $attributes);
        }
        $mform->setType($this->inputname(), PARAM_INT);
        $mform->setDefault($this->inputname(), time());
    }

    /**
     * @return string
     */
    public function data_field() {
        return 'value';
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function display() {
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->name()), ['class' => 'customfieldname']).
               \html_writer::tag('span', userdate($this->get('data')), ['class' => 'customfieldvalue']).
               \html_writer::end_tag('div');
    }

    /**
     * If timestamp is in YYYY-MM-DD or YYYY-MM-DD-HH-MM-SS format, then convert it to timestamp.
     *
     * @param string|int $datetime datetime to be converted.
     * @param stdClass $datarecord The object that will be used to save the record
     * @return int timestamp
     * @since Moodle 2.5
     * @throws \coding_exception
     */
    public function edit_save_data_preprocess($datetime, $datarecord) {
        if (!$datetime) {
            return 0;
        }

        if (is_numeric($datetime)) {
            $gregoriancalendar = \core_calendar\type_factory::get_calendar_instance('gregorian');
            $datetime = $gregoriancalendar->timestamp_to_date_string($datetime, '%Y-%m-%d-%H-%M-%S', 99, true, true);
        }

        $datetime = explode('-', $datetime);
        // Bound year with start and end year.
        // TODO: check it.
        // $datetime[0] = min(max($datetime[0], $this->data->param1), $this->field->param2);

        // !empty($this->field->param3) = configdata['includetime'] ?
        if (count($datetime) == 6) {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2], $datetime[3], $datetime[4], $datetime[5]);
        } else {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2]);
        }
    }
}
