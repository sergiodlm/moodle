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
 * @copyright 2018, Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use core\persistent;
use gradereport_singleview\local\screen\select;

defined('MOODLE_INTERNAL') || die;


/**
 * Class data
 *
 * @package core_customfield
 */
class data extends persistent {
    /**
     * Database data.
     */
    const TABLE = 'customfield_data';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'fieldid' => [
                        'type' => PARAM_TEXT,
                ],
                'recordid' => [
                        'type' => PARAM_TEXT,
                ],
                'intvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'decvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'charvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'shortcharvalue' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'value' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'valueformat' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'contextid' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ]
        );
    }

    /**
     * @param int $fieldid
     * @param int $recordid
     * @return data|null
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function load(int $recordid, int $fieldid) : self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid, 'recordid' => $recordid]);

        return new self($dbdata->id);
    }

    public static function fieldload(int $fieldid) : self {
        global $DB;

        $dbdata = $DB->get_record(self::TABLE, ['fieldid' => $fieldid]);

        return new self($dbdata->id);
    }
}

