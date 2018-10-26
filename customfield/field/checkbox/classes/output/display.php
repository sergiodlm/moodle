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
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_checkbox\output;

use core_customfield\api;
use renderable;
use templatable;
use renderer_base;

defined('MOODLE_INTERNAL') || die;

/**
 * Class management
 *
 * @package core_customfield\output
 */
class display implements renderable, templatable {

    /**
     * Data to be displayed
     * @var
     */
    protected $data;

    /**
     * management constructor.
     *
     * @param \core_customfield\handler $handler
     */
    public function __construct(\core_customfield\data $data) {
        $this->data = $data;
    }

    /**
     * @param renderer_base $output
     * @return array|object|\stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $data = new \stdClass();
        if (api::datafield($this->data->get_field()) === null) {
            $data->fieldname = '';
            $data->fieldvalue = '';
        } else {
            $data->fieldname = format_string($this->data->get_field()->get('name'));
            if (api::datafield($this->data->get_field()) == 0) {
                $data->fieldvalue = get_string('no');
            } else {
                $data->fieldvalue = get_string('yes');
            }
        }
        return $data;
    }
}
