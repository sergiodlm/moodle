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

namespace core_customfield;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class field_config_form
 *
 * @package core_customfield
 */
class field_config_form extends \moodleform {

    /**
     * @throws \coding_exception
     */
    public function definition() {
        global $PAGE;
        $mform = $this->_form;

        $handler = $this->_customdata['handler'];
        if (!$handler || !$handler instanceof handler) {
            throw new \coding_exception('Handler must be passed in customdata');
        }
        $field = $this->_customdata['field'];
        if (!$field || !$field instanceof field) {
            throw new \coding_exception('Field must be passed in customdata');
        }

        $mform->addElement('header', '_commonsettings', get_string('commonsettings', 'core_customfield'));

        $mform->addElement('text', 'name', get_string('fieldname', 'core_customfield'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('name'), 'required');

        $mform->addElement('text', 'shortname', get_string('fieldshortname', 'core_customfield'));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', get_string('shortname'), 'required');

        $desceditoroptions = ['context' => $handler->get_configuration_context()] + $handler->get_description_text_options() ;
        $mform->addElement('editor', 'description_editor', get_string('description', 'core_customfield'), null, $desceditoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        // If field is required.
        $mform->addElement('selectyesno', 'configdata[required]', get_string('isfieldrequired', 'core_customfield'));
        $mform->setType('configdata[required]', PARAM_BOOL);

        // If field data is unique.
        $mform->addElement('selectyesno', 'configdata[uniquevalues]', get_string('isdataunique', 'core_customfield'));
        $mform->setType('configdata[uniquevalues]', PARAM_BOOL);

        // Handler/component settings.
        $mform->addElement('header', '_componentsettings', get_string('componentsettings', 'core_customfield'));

        $handler->add_configdata_settings_to_form($mform);

        // Field specific settings from field type.
        $mform->addElement('header', '_specificsettings', get_string('specificsettings', 'core_customfield'));

        $field->add_field_to_config_form($mform);

        // We add hidden fields.
        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_COMPONENT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }

    /**
     * @param array $data
     * @param array $files
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function validation($data, $files = array()) {
        global $DB;

        $errors = array();

        if (!isset($data['categoryid']) || !$DB->record_exists('customfield_category', array('id' => $data['categoryid']))) {
            $errors['categoryid'] = get_string('formfieldcheckcategoryid', 'core_customfield');
        }

        if (empty($data['id'])) {
            if ($DB->record_exists('customfield_field', array('shortname' => $data['shortname']))) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_customfield');
            }
            $category = new \core_customfield\category($data['categoryid']);
            $handler = \core_customfield\handler::get_handler_for_category($category);
            $record = $handler->new_field($category, $data['type']);
        } else {
            if ($DB->record_exists_select('customfield_field', 'shortname = ? AND id <> ?', array($data['shortname'], $data['id']))) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_customfield');
            }
            $record = \core_customfield\api::get_field($data['id']);
        }
        $errors = array_merge($errors, $record->validate_config_form($data, $files));

        return $errors;
    }
}
