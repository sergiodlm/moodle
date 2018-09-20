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
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_course\customfield;

class course_handler extends \core_customfield\handler {

     public function can_configure() : bool {
         // TODO separate capability
         return has_capability('moodle/category:manage', \context_system::instance());
     }

     public function can_edit($recordid = null) : bool {
         if ($recordid) {
             return has_capability('moodle/course:edit', \context_course::instance($recordid));
         } else {
             //guess_if_creator_will_have_course_capability()
             return true; //TODO
         }
     }

    /**
     * Adds custom fields to edit forms.
     * @param moodleform $mform
     */
    public function display_fields($courseid) {
        $fields = $this->get_fields_with_data($courseid);
        $content = \html_writer::start_tag('div', ['class' => 'customfields-container', 'style' => 'clear: both;']);
        foreach ($fields as $field) {
            if ($field->should_display()) {
                $content .= $field->display();
            }
        }
        $content .= \html_writer::end_tag('div');
        return $content;
    }

    public function get_configuration_url(): \moodle_url {
        return new \moodle_url('/course/customfield.php');
    }
}
