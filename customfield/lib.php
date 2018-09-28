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
 * Callbacks
 *
 * @package   core_customfield
 * @copyright 2018 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

function core_customfield_inplace_editable($itemtype, $itemid, $newvalue) {
    if ($itemtype === 'category') {
        $category = new \core_customfield\category($itemid);
        $handler = \core_customfield\handler::get_handler_for_category($category);
        \external_api::validate_context($handler->get_configuration_context());
        if (!$handler->can_configure()) {
            throw new moodle_exception('nopermissionconfigure', 'core_customfield');
        }
        $newvalue = clean_param($newvalue, PARAM_NOTAGS);
        $category->set('name', $newvalue);
        $category->save();
        return $category->get_inplace_editable(true);
    }
}