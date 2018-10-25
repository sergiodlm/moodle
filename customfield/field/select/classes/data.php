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
 * @package   customfield_select
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_select;

use core\persistent;

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
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {

    }

    /**
     * Return which column from mdl_customfield_data is used to store and retrieve data
     *
     * @return string
     */
    public function datafield() : string {

    }

    /**
     * Validates data for this field.
     *
     * @param \stdClass $data
     * @param array $files
     * @return array
     */
    public function validate_data(\stdClass $data, array $files): array {
        $options = $this->get_options_array();
        $errors = parent::validate_data($data, $files);
        if (isset($data->{$this->inputname()})) {
            if (!isset($options[$data->{$this->inputname()}])) {
                $errors[$this->inputname()] = get_string('invalidoption', 'customfield_select');
            }
        } else {
            $errors[$this->inputname()] = get_string('invalidoption', 'customfield_select');
        }
        return $errors;
    }

    /**
     * Returns the options available as an array.
     *
     * @return array
     */
    public function get_options_array(): array {
        if (isset($this->get_field_configdata()['options'])) {
            $options = explode("\r\n", $this->get_field_configdata()['options']);
        } else {
            $options = array();
        }
        return $options;
    }
}
