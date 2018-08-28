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
 * @copyright 2018, David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class core_cfield_renderer extends plugin_renderer_base {

    protected function render_management(core_cfield\output\management $list) {
        global $PAGE;
        $render = new \core_renderer($PAGE, 'cfield');

        $data = $list->export_for_template($this);

        if ( !empty($data->success) ) {
            $data->alert = $render->notification(base64_decode($data->success), 'success');
        } elseif ( !empty($data->error) ) {
            $data->alert = $render->notification(base64_decode($data->error), 'error');
        }

        return $this->render_from_template('core_cfield/cfield', $data);
    }

    public function load($id) {

    }
}