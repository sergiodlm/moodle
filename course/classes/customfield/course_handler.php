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

use core_customfield\field;

defined('MOODLE_INTERNAL') || die;

/**
 * Course handler for custom fields
 *
 * @package core_course
 */
class course_handler extends \core_customfield\handler {

    /**
     * @var course_handler
     */
    static protected $singleton;

    /**
     * @var \context
     */
    protected $parentcontext;

    const VISIBLETOALL = 2;
    const VISIBLETOTEACHERS = 1;
    const NOTVISIBLE = 0;

    /**
     * Returns a singleton
     *
     * @param int $itemid
     * @return \core_customfield\handler
     */
    public static function instance(int $itemid = 0) : \core_customfield\handler {
        if (static::$singleton === null) {
            self::$singleton = new static(0);
        }
        return self::$singleton;
    }

    /**
     * The current user can configure custom fields on this component.
     *
     * @return bool true if the current can configure custom fields, false otherwise
     */
    public function can_configure() : bool {
        return has_capability('moodle/course:configurecustomfields', $this->get_configuration_context());
    }

    /**
     * The current user can edit custom fields on the given course.
     *
     * @param int $instanceid id of the course to test edit permission
     * @return bool true if the current can edit custom fields, false otherwise
     */
    public function can_edit(field $field, $instanceid = null) : bool {
        if ($instanceid) {
            $context = $this->get_data_context($instanceid);
            return has_capability('moodle/course:update', $context) &&
                (!$field->get_configdata_property('locked') ||
                    has_capability('moodle/course:changelockedcustomfields', $context));
        } else {
            $context = $this->get_parent_context();
            return guess_if_creator_will_have_course_capability('moodle/course:update', $context) &&
            (!$field->get_configdata_property('locked') ||
                guess_if_creator_will_have_course_capability('moodle/course:changelockedcustomfields', $context));
        }
    }

    public function can_view(field $field, $instanceid = null): bool {
        $visibility = $field->get_configdata_property('visibility');
        if ($visibility == self::NOTVISIBLE) {
            return false;
        } else if ($visibility == self::VISIBLETOTEACHERS) {
            return has_capability('moodle/course:update', $this->get_data_context($instanceid));
        } else {
            return true;
        }
    }

    /**
     * Sets parent context for the course
     *
     * This may be needed when course is being created, there is no course context but we need to check capabilities
     *
     * @param \context $context
     */
    public function set_parent_context(\context $context) {
        $this->parentcontext = $context;
    }

    /**
     * Returns the parent context for the course
     *
     * @return \context
     */
    protected function get_parent_context() : \context {
        return $this->parentcontext ?: \context_system::instance();
    }

    /**
     * Adds custom fields to edit forms.
     *
     * @param int $courseid
     */
    public function display_fields($courseid) {
        $visiblefields = $this->get_visible_fields($courseid);
        $fields = $this->get_fields_with_data($visiblefields, $courseid);
        $content = \html_writer::start_tag('div', ['class' => 'customfields-container', 'style' => 'clear: both;']);
        foreach ($fields as $data) {
            $content .= $data->display();
        }
        $content .= \html_writer::end_tag('div');
        return $content;
    }

    /**
     * Context that should be used for new categories created by this handler
     *
     * @return \context the context for configuration
     */
    public function get_configuration_context(): \context {
        return \context_system::instance();
    }

    /**
     * URL for configuration of the fields on this handler.
     *
     * @return \moodle_url The URL to configure custom fields for this component
     */
    public function get_configuration_url(): \moodle_url {
        return new \moodle_url('/course/customfield.php');
    }

    /**
     * Returns the context for the data associated with the given instanceid.
     *
     * @param int $instanceid id of the record to get the context for
     * @return \context the context for the given record
     */
    public function get_data_context(int $instanceid): \context {
        if ($instanceid > 0) {
            return \context_course::instance($instanceid);
        } else {
            return \context_system::instance();
        }
    }

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_to_field_config_form(\MoodleQuickForm $mform) {
        // TODO: should we use a separate category on form for "handler" fields? - Yes (Marina).
        // If field is locked.
        $mform->addElement('selectyesno', 'configdata[locked]', get_string('isfieldlocked', 'core_customfield'));
        $mform->setType('configdata[locked]', PARAM_BOOL);

        // Field data visibility.
        $visibilityoptions = [self::VISIBLETOALL => get_string('everyone', 'core_customfield'),
            self::VISIBLETOTEACHERS => get_string('courseeditors', 'core_customfield'),
            self::NOTVISIBLE => get_string('notvisible', 'core_customfield')];
        $mform->addElement('select', 'configdata[visibility]', get_string('visibility', 'core_customfield'), $visibilityoptions);
        $mform->setType('configdata[visibility]', PARAM_INT);
    }
}
