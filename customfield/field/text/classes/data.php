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
 * @package   core_customfield
 * @copyright 2018 Daniel Neis Araujo <daniel@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_text;

defined('MOODLE_INTERNAL') || die;

/**
 * Class data
 *
 * @package customfield_text
 */
class data extends \core_customfield\data {

    /**
     * Add fields for editing a text profile field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function edit_field_add(\MoodleQuickForm $mform) {
        $config = $this->get_field_configdata();
        $type = ($config['ispassword'] == 1) ? 'password' : 'text';
        $mform->addElement($type, $this->inputname(), format_string($this->get_field()->get('name')));
        $mform->setType($this->inputname(), PARAM_TEXT);
        if (empty($this->get_formvalue()) && !empty($config['defaultvalue'])) {
            $mform->setDefault($this->inputname(), $config['defaultvalue']);
        }
    }

    /**
     * @return string
     */
    public function datafield() : string {
        return 'charvalue';
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function display() {
        $config = $this->get_field_configdata();
        if (!empty($config['link'])) {
            // If link is set show the link.
            $url = str_replace('$$', $this->get_formvalue(), $config['link']);
            $output =  \html_writer::start_tag('span', ['class' => 'customfieldlink']);
            $output .= \html_writer::link($url, $this->get_field()->get('name'), ['target' => $config['linktarget']]);
            $output .= \html_writer::end_tag('span');
        } else {
            // Otherwise show the text.
            $output =  \html_writer::tag('span', format_string($this->get_field()->get('name')), ['class' => 'customfieldname']);
            $output .= \html_writer::tag('span', ': ', ['class' => 'customfieldseparator']);
            $output .= \html_writer::tag('span', format_string($this->get_formvalue()), ['class' => 'customfieldvalue']);
        }
        return \html_writer::tag('div', $output, ['class' => 'customfield customfieldtext']);
    }
}
