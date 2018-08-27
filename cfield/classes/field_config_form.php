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

    // \core_cfield\field $fielddefinition

    public function definition() {
        $mform = $this->_form;

        $this->_customdata['classfieldtype']::add_fields_edit_form($mform);

        $select = $mform->addElement('select', 'categoryid', get_string('category', 'core_cfield'), $this->_customdata['categorylist']);
        $select->setSelected($this->_customdata['categoryid']);

        $mform->addElement('hidden', 'handler', $this->_customdata['handler']);
        $mform->setType('handler', PARAM_RAW);

        $mform->addElement('hidden', 'classfieldtype', $this->_customdata['classfieldtype']);
        $mform->setType('classfieldtype', PARAM_RAW);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_RAW);

        $mform->addElement('hidden', 'action', $this->_customdata['action']);
        $mform->setType('action', PARAM_RAW);

        if (!empty($this->_customdata['id'])) {

            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
            $mform->addElement('hidden', 'itemid', $this->_customdata['id']);
            $mform->setType('itemid', PARAM_INT);

            $this->add_action_buttons(true, get_string('modify', 'core_cfield'));
        } else {
            $this->add_action_buttons(true, get_string('add', 'core_cfield'));
        }
    }


    public function validation($data, $files = array()) {
        global $DB;

        $errors = array();

        if (!empty($this->_customdata['id'])) {
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