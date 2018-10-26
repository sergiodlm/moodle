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
 * @package   customfield_textarea
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_textarea;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\data;
use core_customfield\handler;
use core_customfield\plugin_base;
use tool_dataprivacy\context_instance;

/**
 * Class data
 *
 * @package customfield_textarea
 */
class plugin extends plugin_base {

    const SIZE = 20;
    const DATATYPE = 'value';

    /**
     * Add fields for editing a textarea field.
     *
     * @param \core_customfield\field $field
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_config_form(\core_customfield\field $field, \MoodleQuickForm $mform) {
        global $PAGE;
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_textarea'));
        $mform->setExpanded('header_specificsettings', true);

        $desceditoroptions = self::value_editor_options($field);

        $mform->addElement('editor', 'configdata[defaultvalue_editor]', get_string('defaultvalue', 'core_customfield'),
            null, $desceditoroptions);
    }

    // TODO: move to a trait.
    /**
     * @return string
     */
    public static function datafield() :string {
        return self::DATATYPE;
    }

    /**
     * Add fields for editing a textarea field.
     *
     * @param \moodleform $mform
     * @throws \coding_exception
     */
    public static function edit_field_add(\core_customfield\field $field, \MoodleQuickForm $mform) {
        $desceditoroptions = self::value_editor_options($field);
        $mform->addElement('editor', api::field_inputname($field).'_editor', format_string($field->get('name')), null, $desceditoroptions);
    }

    public static function value_editor_options(\core_customfield\field $field, data $data = null) {
        global $CFG;
        require_once($CFG->libdir.'/formslib.php');
        if ($data) {
            $context = $data->get_context();
        } else {
            $context = handler::get_handler_for_field($field)->get_configuration_context();
        }
        return ['maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'context' => $context];
    }

    /**
     * Prepare the field data to set in the configuration form
     *
     * @param field $field
     * @return \stdClass
     */
    public static function prepare_field_for_form(\core_customfield\field $field) : \stdClass {
        $fieldrecord = parent::prepare_field_for_form($field);

        if (!empty($fieldrecord->configdata['defaultvalue'])) {
            $textoptions = self::value_editor_options($field);
            $context = $textoptions['context'];

            $record = new \stdClass();
            $record->defaultvalue = $fieldrecord->configdata['defaultvalue'];
            $record->defaultvalueformat = $fieldrecord->configdata['defaultvalueformat'];
            file_prepare_standard_editor($record, 'defaultvalue', $textoptions, $context,
                'customfield_textarea', 'defaultvalue', $fieldrecord->id);
            $fieldrecord->configdata['defaultvalue_editor'] = $record->defaultvalue_editor;
        }

        return $fieldrecord;
    }
}