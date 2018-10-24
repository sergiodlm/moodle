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

namespace customfield_checkbox;

use core\persistent;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package customfield_checkbox
 */
class data extends \core_customfield\data {

    /**
     * Add fields for editing a textarea field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {
        $config = $this->get_field_configdata();
        $checkbox = $mform->addElement('advcheckbox', $this->inputname(), format_string($this->get_field()->get('name')));
        if (($this->get_formvalue() == '1') || $config['checkbydefault'] == 1) {
            $checkbox->setChecked(true);
        }
        $mform->setType($this->inputname(), PARAM_BOOL);
    }

    /**
     * @return string
     */
    public function datafield() : string {
        return 'intvalue';
    }
}
