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

namespace core_customfield\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

class provider implements \core_privacy\local\metadata\provider, \core_privacy\local\request\subsystem\plugin_provider {

    // Returns meta data about this system.
    public static function get_metadata(collection $collection) : collection {

    }

    // Writes user data to the writer for the user to download.
    public static function export_customfield(\context $context, string $component, string $commentarea, int $itemid,
            array $subcontext, bool $onlyforthisuser = true) {

    }

    // Deletes all custom fields for a specified context, component, and commentarea.
    public static function delete_customfield_for_all_users(\context $context, string $component, string $commentarea = null,
            int $itemid = null) {

    }

    // Deletes all custom fields for a specified context, component, and commentarea.
    public static function delete_customfield_for_all_users_select(\context $context, string $component, string $commentarea,
            $itemidstest, $params = []) {

    }

    // Deletes all records for a user from a list of approved contexts.
    public static function delete_customfield_for_user(\core_privacy\local\request\approved_contextlist $contextlist,
            string $component, string $commentarea = null, int $itemid = null) {

    }
}
