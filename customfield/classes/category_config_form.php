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

/**
 * Class category_config_form.
 *
 * @package core_customfield
 */
class category_config_form extends \moodleform {

    /**
     * Definition of Category form.
     * @throws \coding_exception
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('categoryname', 'core_customfield'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('name'), 'required');

        $mform->addElement('hidden', 'handler', $this->_customdata['handler']);
        $mform->setType('handler', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true);
    }

    /**
     * Perform validation on categories.
     * @param array $data
     * @param array $files
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function validation($data, $files = array()) {
        global $DB;

        $errors = array();

        if (!empty($data['id'])) {
            if ( $DB->record_exists_select('customfield_category', 'name = ? AND id <> ?', array($data['name'], $data['id']) )) {
                $errors['name'] = get_string('formcategorycheckname', 'core_customfield');
            }
        } else {
            if ( $DB->record_exists_select('customfield_category', 'name = ?', array($data['name']) )) {
                $errors['name'] = get_string('formcategorycheckname', 'core_customfield');
            }
        }
        return $errors;
    }
}
