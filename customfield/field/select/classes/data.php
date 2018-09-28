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
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
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
     * @param moodleform $mform
     * @throws \coding_exception
     */
    public function edit_field_add($mform) {
        $config = $this->get_field_configdata();

        if (isset($config->options)) {
            $options = explode("\r\n", $config->options);
        } else {
            $options = array();
        }
        $formattedoptions = array();
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $formattedoptions[$key] = format_string($option);
        }

        $mform->addElement('select', $this->inputname(), format_string($this->get_field()->get('name')), $formattedoptions);

        if (is_null($this->get_formvalue())) {
            $defaultkey = array_search($config->defaultvalue, $options);
        } else {
            $defaultkey = $this->get_formvalue();
        }
        $mform->setDefault($this->inputname(), $defaultkey);
    }

    /**
     * @return string
     */
    public function datafield() : string {
        return 'intvalue';
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function display() {
        $config = $this->get_field_configdata();

        if (isset($config->options)) {
            $options = explode("\n", $config->options);
        } else {
            $options = array();
        }
        if (is_null($this->get_formvalue())) {
            $displaydata = get_string('notset', 'core_customfield');
        } else {
            $displaydata = format_string($options[$this->get_formvalue()]);
        }
        return \html_writer::start_tag('div') .
               \html_writer::tag('span', format_string($this->get_field()->get('name')), ['class' => 'customfieldname']) .
               ' : ' .
               \html_writer::tag('span', $displaydata, ['class' => 'customfieldvalue']) .
               \html_writer::end_tag('div');
    }
}
