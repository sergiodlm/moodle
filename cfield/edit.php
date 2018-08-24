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

require_once(__DIR__ . '/../config.php');

$handler = required_param('handler', PARAM_RAW);
$itemid = optional_param('itemid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type',null,PARAM_NOTAGS);
$action = optional_param('action', null, PARAM_NOTAGS);
$success = optional_param('success', false, PARAM_BASE64);
$error   = optional_param('error', false, PARAM_BASE64);

switch ($action) {
    case 'editfield':

        $args = array(
                'id'        => $id,
                'handler'   => $handler,
                'action'    => $action,
                'itemid'    => $itemid,
                'type'      => $type,
                'success'   => $success,
                'error'     => $error
        );
        \core_cfield\lib::edit_field($args);

        break;
    case 'editcategory':

        $args = array(
                'id'        => $id,
                'handler'   => $handler,
                'action'    => $action
        );
        \core_cfield\lib::edit_category($args);

        break;
    default:
        die;
}