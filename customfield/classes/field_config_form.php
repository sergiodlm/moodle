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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

class field_config_form extends \moodleform {

    public function definition() {
        global $PAGE;
        $mform = $this->_form;

        $handler = $this->_customdata['handler'];
        if (!$handler || !$handler instanceof handler) {
            throw new \coding_exception('Handler must be passed in customdata');
        }

        $mform->addElement('header', '_commonsettings', get_string('commonsettings', 'core_customfield'));

        $mform->addElement('select', 'categoryid', get_string('category', 'core_customfield'), $this->_customdata['categorylist']);
        $mform->addRule('categoryid', get_string('categoryidrequired', 'core_customfield'), 'required');

        $mform->addElement('text', 'name', get_string('fieldname', 'core_customfield'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('name'), 'required');

        $mform->addElement('text', 'shortname', get_string('fieldshortname', 'core_customfield'));
        $mform->setType('shortname', PARAM_NOTAGS);
        $mform->addRule('shortname', get_string('shortname'), 'required');

        $desceditoroptions = array(
                'trusttext' => true,
                'subdirs' => true,
                'maxfiles' => 5,
                'maxbytes' => 0,
                'context' => $PAGE->context,
                'noclean' => 0,
                'enable_filemanagement' => true);

        $mform->addElement('editor', 'description_editor', get_string('description', 'core_customfield'), null, $desceditoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        // If field is required.
        $mform->addElement('selectyesno', 'required', get_string('isfieldrequired', 'core_customfield'));

        // If field is locked.
        $mform->addElement('selectyesno', 'locked', get_string('isfieldlocked', 'core_customfield'));

        // If field data is unique.
        $mform->addElement('selectyesno', 'uniquevalues', get_string('isdataunique', 'core_customfield'));

        // Field data visibility.
        $visibilityoptions = [get_string('notvisible', 'core_customfield'),
                              get_string('courseeditors', 'core_customfield'),
                              get_string('everyone', 'core_customfield')];
        $mform->addElement('select', 'visibility', get_string('visibility', 'core_customfield'), $visibilityoptions);

        // We add specific settings here.
        $mform->addElement('header', '_specificsettings', get_string('specificsettings', 'core_customfield'));

        // We load specific fields from type.
        $this->_customdata['classfieldtype']::add_field_to_edit_form($mform);

        // We add hidden fields.
        $mform->addElement('hidden', 'handler', get_class($handler));
        $mform->setType('handler', PARAM_RAW);

        $mform->addElement('hidden', 'itemid', $handler->get_item_id());
        $mform->setType('itemid', PARAM_INT);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_NOTAGS);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }

    public function validation($data, $files = array()) {
        global $DB;

        $errors = array();

        //If we need create Other Fields.
        if (! isset( $data['categoryid'] ) ) {
            $data['categoryid'] = 0;
        }

        if (!empty($data['id'])) {
            if ( $DB->record_exists_select('customfield_field', 'shortname = ? AND id <> ? AND categoryid = ?', array($data['shortname'], $data['id'], $data['categoryid']) )) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_customfield');
            }
        } else {
            if ( $DB->record_exists_select('customfield_field', 'shortname = ? AND categoryid = ?', array($data['shortname'], $data['categoryid']) )) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_customfield');
            }
        }
        return $errors;
    }
}
