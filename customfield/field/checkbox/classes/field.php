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
 * @package   customfield_checkbox
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_checkbox;

/**
 * Class field
 *
 * @package customfield_checkbox
 */
class field extends \core_customfield\field{

    const TYPE = 'checkbox';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_field_to_edit_form( \MoodleQuickForm $mform) {
        $mform->addElement('selectyesno', 'configdata[checkbydefault]', get_string('checkbydefault', 'core_customfield'));    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add($mform) {
        $mform->addElement('checkbox', $this->inputname(), format_string($this->get('name')));
    }

    /**
     * @param $data
     * @throws \coding_exception
     */
    public function set_data($data) {
        // TODO: verify if should support checkboxes with custom values.
        $this->set('data', $data->intvalue);
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
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->name()), ['class' => 'customfieldname']).
               // TODO: show as checkbox, disabled or icon.
               \html_writer::tag('span', $this->get('data'), ['class' => 'customfieldvalue']).
               \html_writer::end_tag('div');
    }
}
