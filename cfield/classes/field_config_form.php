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
 * @package   core_cfield
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_cfield;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

class field_config_form extends \moodleform {

    public function definition() {
        global $PAGE;
        $mform = $this->_form;

        // We add common settings here.
        $mform->addElement('header', '_commonsettings', get_string('commonsettings', 'core_cfield'));

        $mform->addElement('text', 'name', get_string('fieldname', 'core_cfield'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('name'), 'required');

        $mform->addElement('text', 'shortname', get_string('fieldshortname', 'core_cfield'));
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

        $mform->addElement('editor', 'description_editor', get_string('description', 'core_cfield'), null, $desceditoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        // Category list.
        $select = $mform->addElement('select', 'categoryid', get_string('category', 'core_cfield'), $this->_customdata['categorylist']);

        // If field is required.
        $yesnolist = [0 => get_string('no', 'core_cfield'), 1 => get_string('yes', 'core_cfield')];

        $mform->addElement('select', 'configdata[required]', get_string('isfieldrequired', 'core_cfield'), $yesnolist);
        //$select->setSelected($this->_customdata['required']);

        // We add specific settings here.
        $mform->addElement('header', '_specificsettings', get_string('specificsettings', 'core_cfield'));

        // We load specific fields from type.
        $this->_customdata['classfieldtype']::add_field_to_edit_form($mform);

        // We add hidden fields.
        $mform->addElement('hidden', 'handler', $this->_customdata['handler']);
        $mform->setType('handler', PARAM_RAW);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_NOTAGS);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }

    public function validation($data, $files = array()) {
        global $DB;

        $errors = array();

        if (!empty($data['id'])) {
            if ( $DB->record_exists_select('cfield_field', 'shortname = ? AND id <> ? AND categoryid = ?', array($data['shortname'], $data['id'], $data['categoryid']) )) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_cfield');
            }
        } else {
            if ( $DB->record_exists_select('cfield_field', 'shortname = ? AND categoryid = ?', array($data['shortname'], $data['categoryid']) )) {
                $errors['shortname'] = get_string('formfieldcheckshortname', 'core_cfield');
            }
        }
        return $errors;
    }
}
