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
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
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
     * @param moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add($mform) {
        $config = $this->get_field_configdata();
        $checkbox = $mform->addElement('advcheckbox', $this->inputname(), format_string($this->get_field()->get('name')));
        if (($this->get_data() == '1') || $config->checkbydefault == 1) {
            $checkbox->setChecked(true);
        }
        $mform->setType($this->inputname(), PARAM_BOOL);
    }

    /**
     * @param $data
     * @throws \coding_exception
     */
    public function set_data($data) {
        $this->data = $data->intvalue;
    }

    /**
     * @return string
     */
    public function datafield() {
        return 'intvalue';
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function display() {
        global $OUTPUT;
        if ($this->get_data()) {
            $displaydata = $OUTPUT->pix_icon('checked', get_string('checked', 'core_customfield'), 'customfield_date');
        } else {
            $displaydata = $OUTPUT->pix_icon('notchecked', get_string('notchecked', 'core_customfield'), 'customfield_date');
        }
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->get_field()->get('name')), ['class' => 'customfieldname']).
               ' : '.
               \html_writer::tag('span', $displaydata, ['class' => 'customfieldvalue']).
               \html_writer::end_tag('div');
    }
}
