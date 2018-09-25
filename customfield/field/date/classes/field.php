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

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package customfield_date
 */
class field extends \core_customfield\field {
    const TYPE = 'date';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_field_to_config_form( \MoodleQuickForm $mform) {
        // Get the current calendar in use - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        // Create variables to store start and end.
        list($year, $month, $day) = explode('_', date('Y_m_d'));
        $currentdate = $calendartype->convert_from_gregorian($year, $month, $day);
        $currentyear = $currentdate['year'];

        $arryears = $calendartype->get_years();

        $config = json_decode($this->get('configdata'));

        // Add elements.
        $mform->addElement('select', 'configdata[startyear]', get_string('startyear', 'core_customfield'), $arryears);
        $mform->setType('configdata[startyear]', PARAM_INT);

        $defaultstart = isset($config->startyear) ? $config->startyear : $currentyear;
        $mform->setDefault('configdata[startyear]', $defaultstart);

        $mform->addElement('select', 'configdata[endyear]', get_string('endyear', 'core_customfield'), $arryears);
        $mform->setType('configdata[endyear]', PARAM_INT);
        $defaultend = isset($config->endyear) ? $config->endyear : $currentyear;
        $mform->setDefault('configdata[endyear]', $defaultend);

        $mform->addElement('checkbox', 'configdata[includetime]', get_string('includetime', 'core_customfield'));
        $mform->setDefault('configdata[includetime]', isset($config->includetime));

        $mform->addElement('hidden', 'startday', '1');
        $mform->setType('startday', PARAM_INT);
        $mform->addElement('hidden', 'startmonth', '1');
        $mform->setType('startmonth', PARAM_INT);
        $mform->addElement('hidden', 'startyear', '1');
        $mform->setType('startyear', PARAM_INT);
        $mform->addElement('hidden', 'endday', '1');
        $mform->setType('endday', PARAM_INT);
        $mform->addElement('hidden', 'endmonth', '1');
        $mform->setType('endmonth', PARAM_INT);
        $mform->addElement('hidden', 'endyear', '1');
        $mform->setType('endyear', PARAM_INT);
    }

    /**
     * Validate the data from the config form.
     *
     * @param array data from the add/edit custom field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function validate_config_form(array $data, $files = array()) : array {
        $errors = array();

        // Make sure the start year is not greater than the end year.
        if ($data['configdata']['startyear'] > $data['configdata']['endyear']) {
            $errors['configdata_startyear'] = get_string('startyearafterend', 'core_customfield');
        }

        return $errors;
    }

    /**
     * Add fields for editing data of a textarea field on a context.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\moodleform $mform) {
        // Get the current calendar in use - see MDL-18375.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        $config = json_decode($this->get('configdata'));

        $attributes = ['optional' => ($config->required != 1)];

        if (!empty($config->includetime)) {
            $element = 'date_time_selector';
        } else {
            $element = 'date_selector';
        }
        $mform->addElement($element, $this->inputname(), format_string($this->get('name'), $attributes));
        $mform->setType($this->inputname(), PARAM_INT);
        $mform->setDefault($this->inputname(), time());
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
    public function edit_save_data_preprocess(string $data, \stdClass $datarecord) {
        if (!$datetime) {
            return 0;
        }

        if (is_numeric($datetime)) {
            $gregoriancalendar = \core_calendar\type_factory::get_calendar_instance('gregorian');
            $datetime = $gregoriancalendar->timestamp_to_date_string($datetime, '%Y-%m-%d-%H-%M-%S', 99, true, true);
        }

        $config = json_decode($this->get('configdata'));

        $datetime = explode('-', $datetime);
        $datetime[0] = min(max($datetime[0], $config['minyear']), $config['maxyear']);

        if (!empty($config['includetime']) && count($datetime) == 6) {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2], $datetime[3], $datetime[4], $datetime[5]);
        } else {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2]);
        }
    }
}
