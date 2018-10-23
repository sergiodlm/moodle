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

defined('MOODLE_INTERNAL') || die;

/**
 * Class category
 *
 * @package core_customfield
 */
class category extends persistent {
    /**
     * Database table.
     */
    const TABLE = 'customfield_category';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'name' => [
                        'type' => PARAM_TEXT,
                ],
                'description' => [
                        'type' => PARAM_RAW,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'descriptionformat' => [
                        'type' => PARAM_INT,
                        'default' => FORMAT_MOODLE,
                        'optional' => true
                ],
                'component' => [
                        'type' => PARAM_TEXT
                ],
                'area' => [
                        'type' => PARAM_TEXT
                ],
                'itemid' => [
                        'type' => PARAM_INT,
                        'optional' => true,
                        'default' => 0
                ],
                'contextid' => [
                        'type' => PARAM_INT,
                        'optional' => false
                ],
                'sortorder' => [
                        'type' => PARAM_INT,
                        'optional' => false,
                        'default' => -1
                ],
        );
    }

    /**
     * @return field[]
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function fields() {
        return field::get_fields_from_category_array($this->get('id'));
    }

    /**
     * Hook to execute after an update.
     *
     * @param bool $result Whether or not the update was successful.
     * @return void
     * @throws \moodle_exception
     */
    protected function after_update($result) {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
    }

    /**
     * Updates sort order after create
     *
     * @return void
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    protected function after_create() {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
        api::move_category($this, 0);
    }

    /**
     * Delete fields before delete category
     *
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function before_delete() {
        foreach ($this->fields() as $field) {
            $field->delete();
        }
    }

    /**
     * Update sort order after delete
     *
     * @param bool $result
     * @return void
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) {
        handler::get_handler_for_category($this)->clear_fields_definitions_cache();
    }

}
