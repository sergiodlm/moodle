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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");

class core_customfield_external extends external_api {

    public static function delete_entry_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'Entry ID to delete', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function delete_entry($id, $handler) {
        $handler1 = new $handler();
        if( $handler1->can_edit() ) {
            $record = \core_customfield\field_factory::load($id);
            $record->delete();
        }
    }

    public static function delete_entry_returns() {
    }

    public static function reload_template_parameters() {
        return new external_function_parameters(
                array('handler' => new external_value(PARAM_RAW, 'handler', VALUE_REQUIRED))
        );
    }

    public static function reload_template($handler) {
        global $PAGE;

        require_login();
        $PAGE->set_context(context_system::instance());

        $handlerparam = new  $handler();
        $output = $PAGE->get_renderer('core_customfield');
        $outputpage = new \core_customfield\output\management($handlerparam);
        return $outputpage->export_for_template($output);
    }

    public static function reload_template_returns() {
        return new external_single_structure(
            array(
                'handler' => new external_value(PARAM_RAW, 'handler'),
                'customfield' => new external_value(PARAM_RAW, 'customfield'),
                'type' => new external_value(PARAM_RAW, 'type'),
                'categories' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'name'),
                            'id' => new external_value(PARAM_RAW, 'id'),
                            'editcategoryurl' => new external_value(PARAM_NOTAGS, 'edit category url'),
                            'deletecategoryurl' => new external_value(PARAM_NOTAGS, 'delete category url'),
                            'deleteicon' => new external_value(PARAM_RAW, 'delete icon'),
                            'editicon' => new external_value(PARAM_RAW, 'edit icon'),
                            'upiconcategory' => new external_value(PARAM_RAW, 'up icon'),
                            'downiconcategory' => new external_value(PARAM_RAW, 'down icon'),
                            'fields' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'name' => new external_value(PARAM_RAW, 'name'),
                                        'type' => new external_value(PARAM_RAW, 'type'),
                                        'editfieldurl' => new external_value(PARAM_NOTAGS, 'edit field url'),
                                        'editicon' => new external_value(PARAM_RAW, 'edit icon'),
                                        'deletefieldurl' => new external_value(PARAM_NOTAGS, 'deleteurl'),
                                        'deleteicon' => new external_value(PARAM_RAW, 'deleteicon'),
                                        'id' => new external_value(PARAM_RAW, 'id'),
                                        'upiconfield' => new external_value(PARAM_RAW, 'up icon'),
                                        'downiconfield' => new external_value(PARAM_RAW, 'down icon'),
                                    )
                                )
                            , '', VALUE_OPTIONAL),
                        )
                    )
                ),
            )
        );
    }

    public static function delete_category_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'category ID to delete', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function delete_category($id, $handler) {
        $handler1 = new $handler();
        if ($handler1->can_edit()) {
            $record = \core_customfield\category::load($id);
            $record->delete();
        }
    }

    public static function delete_category_returns() {
    }

    public static function move_up_field_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'Entry ID to move up', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function move_up_field($id, $handler) {
        $handler1 = new $handler();
        if ($handler1->can_configure($id)) {
            $record = \core_customfield\field_factory::load($id);
            $record->up();
        }
    }

    public static function move_up_field_returns() {
    }

    public static function move_down_field_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'Entry ID to move down', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function move_down_field($id, $handler) {
        $handler1 = new $handler();
        if ($handler1->can_configure()) {
            $record = \core_customfield\field_factory::load($id);
            $record->down();
        }
    }

    public static function move_down_field_returns() {
    }

    public static function move_up_category_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'Entry ID to move up', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function move_up_category($id, $handler) {
        $handler1 = new $handler();
        if ($handler1->can_configure()) {
            $record = \core_customfield\category::load($id);
            $record->up();
        }
    }

    public static function move_up_category_returns() {
    }

    public static function move_down_category_parameters() {
        return new external_function_parameters(
                array('id' => new external_value(PARAM_INT, 'Entry ID to move down', VALUE_REQUIRED),
                      'handler' => new external_value(PARAM_RAW, 'Handler', VALUE_REQUIRED))
        );
    }

    public static function move_down_category($id, $handler) {
        $handler1 = new $handler();
        if ($handler1->can_configure()) {
            $record = \core_customfield\category::load($id);
            $record->down();
        }
    }

    public static function move_down_category_returns() {
    }
}