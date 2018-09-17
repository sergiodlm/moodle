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
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_select;

class field extends \core_customfield\field{

    const TYPE = 'select';
    const SIZE = 40;

    /**
     * Add fields for editing a text field.
     * @param moodleform $mform
     */
    public static function add_field_to_edit_form( \MoodleQuickForm $mform) {
        $mform->addElement('textarea', 'configdata[options]', 'Menu options (one per line)');
    }

    /**
     * Add fields for editing a textarea field.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $configdata = json_decode($this->get('configdata'));

        if (isset($configdata->options)) {
            $options = explode("\n", $configdata->options);
        } else {
            $options = array();
        }

        $mform->addElement('select', $this->inputname(), format_string($this->get('name')), $options);
        $mform->setDefault($this->inputname(), $this->data);
    }

    public function set_data($data) {
        $this->data = $data->intvalue;
    }

    public function datafield() {
        return 'intvalue';
    }

    public function display() {
        $configdata = json_decode($this->get('configdata'));

        if (isset($configdata->options)) {
            $options = explode("\n", $configdata->options);
        } else {
            $options = array();
        }
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->name()), ['class' => 'customfieldname']).
               \html_writer::tag('span', format_text($options[$this->data]), ['class' => 'customfieldvalue']).
               \html_writer::end_tag('div');
    }

    public function edit_load_data($data) {
        if ($this->data !== null) {
            $data->{$this->inputname()} = $this->data;
        }
    }
}
