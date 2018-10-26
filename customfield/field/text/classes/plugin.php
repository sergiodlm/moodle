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
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_text;

defined('MOODLE_INTERNAL') || die;

use core_customfield\api;
use core_customfield\plugin_base;

/**
 * Class data
 *
 * @package customfield_text
 */
class plugin extends plugin_base {

    const SIZE = 20;
    const DATATYPE = 'charvalue';

    /**
     * Add fields for editing a text field.
     *
     * @param \core_customfield\field $field
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function add_field_to_config_form(\core_customfield\field $field, \MoodleQuickForm $mform) {

        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_text'));
        $mform->setExpanded('header_specificsettings', true);

        $mform->addElement('text', 'configdata[defaultvalue]', get_string('defaultvalue', 'core_customfield'),
                           ['size' => self::SIZE]);
        $mform->setType('configdata[defaultvalue]', PARAM_TEXT);

        $mform->addElement('text', 'configdata[displaysize]', get_string('displaysize', 'customfield_text'), ['size' => 6]);
        $mform->setType('configdata[displaysize]', PARAM_INT);

        $mform->addElement('text', 'configdata[maxlength]', get_string('maxlength', 'customfield_text'), ['size' => 6]);
        $mform->setType('configdata[maxlength]', PARAM_INT);

        $mform->addElement('selectyesno', 'configdata[ispassword]', get_string('profilefieldispassword', 'admin'));
        $mform->setType('configdata[ispassword]', PARAM_INT);

        $mform->addElement('text', 'configdata[link]', get_string('link', 'core_customfield'));
        $mform->setType('configdata[link]', PARAM_URL);
        $mform->addHelpButton('configdata[link]', 'profilefieldlink', 'admin');

        $linkstargetoptions = array(
                ''       => get_string('none', 'customfield_text'),
                '_blank' => get_string('newwindow', 'customfield_text'),
                '_self'  => get_string('sameframe', 'customfield_text'),
                '_top'   => get_string('samewindow', 'customfield_text')
        );
        $mform->addElement('select', 'configdata[linktarget]', get_string('linktarget', 'customfield_text'),
                           $linkstargetoptions);
    }

    // TODO: move to a trait.
    /**
     * Return plugin data type.
     *
     * @return string
     */
    public static function datafield(): string {
        return self::DATATYPE;
    }

    /**
     * Add fields for editing a text profile field.
     *
     * @param \MoodleQuickForm $mform
     * @throws \coding_exception
     */
    public static function edit_field_add(\core_customfield\field $field, \MoodleQuickForm $mform) {
        $config = $field->get('configdata');
        $type = ($config['ispassword'] == 1) ? 'password' : 'text';
        $mform->addElement($type, api::field_inputname($field), format_string($field->get('name')));
        $mform->setType(api::field_inputname($field), PARAM_TEXT);
        // TODO what are these dies for?
        if (empty(api::datafield($field)) && !empty($config['defaultvalue'])) {die;
            $mform->setDefault(api::field_inputname($field), $config['defaultvalue']);die;
        }
    }

}
