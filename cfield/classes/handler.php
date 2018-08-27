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

abstract class handler {

    protected $itemid;

    public function __construct($itemid = null, $component = null) {
        $this->itemid = $itemid;
        $this->component = $component;
    }

    abstract public function get_component() : string;

    abstract public function get_area() : string;

    public function get_item_id() { return $this->itemid; }

    public function uses_item_id() : bool { return false; }

    public function uses_categories() : bool { return true; }

    public function get_category_config_form($action,$args) : \core_cfield\category_config_form {
        return new \core_cfield\category_config_form($action,$args);
    }

    public function get_field_config_form($action,$args) : \core_cfield\field_config_form {
        return new \core_cfield\field_config_form($action,$args);
    }

    abstract public function can_configure($itemid = null) : bool;

    abstract public function can_edit($recordid = null, $itemid = null) : bool;

    public function is_field_supported(\core_cfield\field $field) : bool {
        // Placeholder for now to allow in the future components to decide that they don't want to support some field types.
        return true;
    }

    public function get_fields_definitions() {
        $fields = api::get_fields_definitions(
                $this->get_component(),
                $this->get_area(),
                $this->get_item_id()
        );
        return $fields;
        //return array_filter($fields, [$this, 'is_field_supported']);
    }

}
