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
 * @package   customfield_text
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_text;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package customfield_text
 */
class field extends \core_customfield\field {

    const SIZE = 20;

    /**
     * Add fields for editing a text field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public function add_field_to_config_form(\MoodleQuickForm $mform) {

        $mform->addElement('text', 'configdata[defaultvalue]', get_string('defaultvalue', 'core_customfield'), ['size' => self::SIZE]);
        $mform->setType('configdata[defaultvalue]', PARAM_TEXT);

        $mform->addElement('text', 'configdata[displaysize]', get_string('displaysize', 'core_customfield'), ['size' => 6]);
        $mform->setType('configdata[displaysize]', PARAM_INT);

        $mform->addElement('text', 'configdata[maxlength]', get_string('maxlength', 'core_customfield'), ['size' => 6]);
        $mform->setType('configdata[maxlength]', PARAM_INT);

        $mform->addElement('selectyesno', 'configdata[ispassword]', get_string('profilefieldispassword', 'admin'));
        $mform->setType('configdata[ispassword]', PARAM_INT);

        $mform->addElement('text', 'configdata[link]', get_string('link', 'core_customfield'));
        $mform->setType('configdata[link]', PARAM_URL);
        $mform->addHelpButton('configdata[link]', 'profilefieldlink', 'admin');

        $linkstargetoptions = array(
                ''       => get_string('none', 'core_customfield'),
                '_blank' => get_string('newwindow', 'core_customfield'),
                '_self'  => get_string('sameframe', 'core_customfield'),
                '_top'   => get_string('samewindow', 'core_customfield')
        );
        $mform->addElement('select', 'configdata[linktarget]', get_string('linktarget', 'core_customfield'), $linkstargetoptions);
    }
}
