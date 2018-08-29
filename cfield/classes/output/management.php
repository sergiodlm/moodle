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

namespace core_cfield\output;

use renderable;
use templatable;
use renderer_base;

defined('MOODLE_INTERNAL') || die;

class management implements renderable, templatable{

    protected $handler;
    protected $categoryid;

    public function __construct(\core_cfield\handler $handler) {
        $this->handler = $handler;
    }

    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = (object) [];

        $data->customfield = get_string('customfield', 'core_cfield');
        $data->type = get_string('type', 'core_cfield');
        $data->handler = get_class($this->handler);
        $data->link = new \moodle_url('/cfield/edit.php', array('handler' => $data->handler, 'action' => 'editfield'));
        $data->createnewcfield = get_string('createnewcfield', 'core_cfield');

        $categories = $this->handler->get_fields_definitions();

        $deleteicon = $OUTPUT->pix_icon('t/delete',get_string('delete'));
        $editicon = $OUTPUT->pix_icon('t/edit',get_string('edit'));

        $categoriesarray = array();

        foreach ($categories as $category)
        {
            $categoryarray = array();
            $categoryarray['id'] =$category->get_id();
            $categoryarray['name'] = $category->get_name();
            $categoryarray['customfield'] = get_string('customfield', 'core_cfield');
            $categoryarray['deleteicon'] = $deleteicon;
            $categoryarray['editicon'] = $editicon;

            $categoryarray['deletecategoryurl'] = (string)new \moodle_url('/cfield/edit.php', [
                    'deletecategory' => $categoryarray['id'],
                    'handler' => $data->handler,
                    'sesskey' => sesskey()
            ]);

            $categoryarray['editcategoryurl'] = (string)new \moodle_url('/cfield/edit.php', [
                    'id' => $categoryarray['id'],
                    'handler' => $data->handler,
                    'action' => 'editcategory',
            ]);

            foreach ($category->get_fields() as $field)
            {
                global $OUTPUT;

                $fieldarray['type'] = $field->get_type();
                $fieldarray['id'] = $field->get_id();
                $fieldarray['name'] = $field->get_name();
                $fieldarray['deleteicon'] = $deleteicon;
                $fieldarray['editicon'] = $editicon;

                $fieldarray['deletefieldurl'] = (string)new \moodle_url('/cfield/edit.php', [
                       'delete' => $fieldarray['id'],
                       'handler' => $data->handler,
                       'type' => $fieldarray['type'],
                       'sesskey' => sesskey()
               ]);

                $fieldarray['editfieldurl'] = (string)new \moodle_url('/cfield/edit.php', [
                        'id' => $fieldarray['id'],
                        'handler' => $data->handler,
                        'type' => $fieldarray['type'],
                        'action' => 'editfield'
                ]);

                $categoryarray['fields'][] = $fieldarray;
            }
            $categoriesarray[] = $categoryarray;
        }

        $data->categories = $categoriesarray;

        // Create a new dropdown for types of fields.
        $options = ['text' => 'Text Input','textarea'=>'Text Area'];
        $select = new \single_select($data->link, 'type', $options, '', array('' => get_string('choosedots')), 'newfieldform');
        $data->singleselect = $select->export_for_template($output);

        // Create a new category link.
        $options = array('action' => 'editcategory', 'handler' => $data->handler);
        $data->singlebutton = $OUTPUT->single_button(new \moodle_url('/cfield/edit.php', $options), get_string('createnewccategory', 'core_cfield'));

        return $data;
    }
}