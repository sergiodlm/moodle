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
 * Defines the backup_enrol_lti_plugin class.
 *
 * @package   customfield_text
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/backup/moodle2/backup_customfield_plugin.class.php');

/**
 * Define all the backup steps.
 *
 * @package   customfield_text
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_customfield_text_plugin extends backup_customfield_plugin {

    /**
     * Defines the other Text Custom Field structures to append.
     *
     * @return backup_plugin_element
     */
    public function define_enrol_plugin_structure() : backup_plugin_element {
        // Get the parent we will be adding these elements to.
        $plugin = $this->get_plugin_element();

        // Define our elements.
        $categories= new backup_nested_element('categories');

        $category = new backup_nested_element('category', array('id'), array(
            'name', 'description', 'descriptionformat', 'sortorder', 'component', 'area', 'itemid',
            'timecreated', 'timemodified'));

        $fields = new backup_nested_element('fields');

        $field = new backup_nested_element('field', array('id'), array(
            'shortname', 'name', 'type', 'description', 'descriptionformat', 'required', 'locked',
            'uniquevalues', 'visibility', 'sortorder', 'categoryid', 'configdata', 'timecreated',
            'timemodified'));

        $data = new_backup_nested_element('data', array('id'), array(
        ));

        $plugin->add_child($categories);
        $categories->add_child($category);
        $category->add_child($fields);
        $fields->add_child($field);

        // Set sources to populate the data.
        $category->set_source_table('customfield_category',
            // TODO: how to associate with course?
            array('contextid' => backup::VAR_PARENTID));

        $field->set_source_table('customfield_field', array('categoryid' => backup::VAR_PARENTID));

        $data->set_source_table('customfield_data', array('fieldid' => backup::VAR_PARENTID));
    }
}
