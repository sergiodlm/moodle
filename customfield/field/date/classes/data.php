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
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_date;

use core_customfield\api;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package customfield_date
 */
class data extends \core_customfield\data {

    /**
     * Add fields for editing data of a textarea field on a context.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {

    }

    /**
     * If timestamp is in YYYY-MM-DD or YYYY-MM-DD-HH-MM-SS format, then convert it to timestamp.
     *
     * @param string|array $data
     * @return mixed
     * @throws \coding_exception
     */
    protected function preprocess($data) {
        if (!$data) {
            return 0;
        }

        // TODO why???

        if (is_numeric($data)) {
            $gregoriancalendar = \core_calendar\type_factory::get_calendar_instance('gregorian');
            $datetime = $gregoriancalendar->timestamp_to_date_string($data, '%Y-%m-%d-%H-%M-%S', 99, true, true);
        }

        $config = $this->get_field()->get('configdata');

        $datetime = explode('-', $datetime);
        $datetime[0] = min(max($datetime[0], $config['startyear']), $config['endyear']);

        if (!empty($config['includetime']) && count($datetime) == 6) {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2], $datetime[3], $datetime[4], $datetime[5]);
        } else {
            return make_timestamp($datetime[0], $datetime[1], $datetime[2]);
        }
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
        $value = $this->preprocess($datanew->{api::field_inputname($this->get_field())});
        $this->set(api::datafield($this->get_field()), $value);
        $this->set('value', $value);
        $this->save();
        return $this;
    }
}
