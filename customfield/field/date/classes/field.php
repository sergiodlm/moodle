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

    public function add_field_to_config_form( \MoodleQuickForm $mform) {

    }

    /**
     * Validate the data from the config form.
     *
     * @param array $data
     * @param array $files
     * @return array associative array of error messages
     * @throws \coding_exception
     */
    public function validate_config_form(array $data, $files = array()) : array {
        $errors = array();

        // Make sure the start year is not greater than the end year.
        if ($data['configdata']['startyear'] > $data['configdata']['endyear']) {
            $errors['configdata[startyear]'] = get_string('startyearafterend', 'customfield_date');
        }

        return $errors;
    }
}
