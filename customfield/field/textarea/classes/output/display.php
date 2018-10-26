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

namespace customfield_textarea\output;

use core_customfield\api;
use core_customfield\data;
use core_customfield\handler;
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
     * @var data
     */
    protected $data;

    /**
     * management constructor.
     *
     * @param data $data
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

        $fieldid = $this->data->get_field()->get('id');
        $instanceid = $this->data->get('instanceid');
        $handler = handler::get_handler_for_field($this->data->get_field());
        $configcontext = $handler->get_configuration_context()->id;

        if ($dataid = $this->data->get('id')) {
            $context = $handler->get_data_context($instanceid)->id;
            $processed = file_rewrite_pluginfile_urls($this->data->get('value'), 'pluginfile.php',
                $context, 'customfield_textarea', 'value', $dataid);
            $value = format_text($processed, $this->data->get('valueformat'), ['context' => $context]);
        } else {
            $processed = file_rewrite_pluginfile_urls($this->data->get_field()->get_configdata_property('defaultvalue'),
                'pluginfile.php',
                $configcontext, 'customfield_textarea', 'defaultvalue', $fieldid);
            $value = format_text($processed, $this->data->get('valueformat'), ['context' => $configcontext]);
        }
        $data = new \stdClass();
        $data->fieldname = format_string($this->data->get_field()->get('name'), true, ['context' => $configcontext]);
        $data->fieldvalue = $value;

        return $data;
    }
}
