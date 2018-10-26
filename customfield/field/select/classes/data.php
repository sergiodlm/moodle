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
use core_customfield\api;

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
     * Validates data for this field.
     *
     * @param \stdClass $data
     * @param array $files
     * @return array
     */
    public function validate_data(\stdClass $data, array $files): array {
        $options = plugin::get_options_array($this->get_field());
        $errors = parent::validate_data($data, $files);
        if (isset($data->{api::field_inputname($this->get_field())})) {
            if (!isset($options[$data->{api::field_inputname($this->get_field())}])) {
                $errors[api::field_inputname($this->get_field())] = get_string('invalidoption', 'customfield_select');
            }
        } else {
            $errors[api::field_inputname($this->get_field())] = get_string('invalidoption', 'customfield_select');
        }
        return $errors;
    }
}
