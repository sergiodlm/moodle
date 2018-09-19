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

namespace core_customfield\output;

use renderable;
use templatable;
use renderer_base;

defined('MOODLE_INTERNAL') || die;

class management implements renderable, templatable {

    protected $handler;
    protected $categoryid;

    public function __construct(\core_customfield\handler $handler) {
        $this->handler = $handler;
    }

    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = (object) [];

        $fieldtypes = $this->handler->field_types();

        $data->handler = get_class($this->handler);
        $data->itemid = $this->handler->get_item_id();
        $addfieldurl = new \moodle_url('/customfield/edit.php',
            array('handler' => $data->handler, 'itemid' => $data->itemid, 'action' => 'editfield'));

        $categories = $this->handler->get_fields_definitions();

        $categoriesarray = array();

        foreach ($categories as $category) {

            $categoryarray = array();
            $categoryarray['id'] = $category->id();
            $categoryarray['name'] = $category->name();
            $categoryarray['customfield'] = get_string('customfield', 'core_customfield');
            $categoryarray['action'] = get_string('action', 'core_customfield');

            $categoryarray['deletecategoryurl'] = (new \moodle_url('/customfield/edit_category.php', [
                    'deletecategory' => $categoryarray['id'],
                    'handler' => $data->handler,
                    'itemid' => $data->itemid,
                    'sesskey' => sesskey()
            ]))->out(false);

            $categoryarray['editcategoryurl'] = (new \moodle_url('/customfield/edit_category.php', [
                    'id' => $categoryarray['id'], 'handler' => $data->handler, 'itemid' => $data->itemid
            ]))->out(false);

            foreach ($category->fields() as $field) {
                global $OUTPUT;

                $fieldarray['type'] = $fieldtypes[$field->type()];
                $fieldarray['id'] = $field->id();
                $fieldarray['name'] = $field->name();
                $fieldarray['shortname'] = $field->shortname();

                $fieldarray['deletefieldurl'] = (new \moodle_url('/customfield/edit.php', [
                        'delete' => $fieldarray['id'],
                        'handler' => $data->handler,
                        'itemid' => $data->itemid,
                        'type' => $fieldarray['type'],
                        'sesskey' => sesskey()
                ]))->out(false);

                $fieldarray['editfieldurl'] = (new \moodle_url('/customfield/edit.php', [
                        'id' => $fieldarray['id'],
                        'handler' => $data->handler,
                        'itemid' => $data->itemid,
                        'type' => $fieldarray['type'],
                ]))->out(false);

                $categoryarray['fields'][] = $fieldarray;
                //$fieldexporter = new \core_customfield\list_exporter($fieldarray);
                //$categoryarray['fields'][] = $fieldexporter->export($output);
            }
            $categoriesarray[] = $categoryarray;
        }

        $data->categories = $categoriesarray;

        if (empty($data->categories)) {
            $url = new \moodle_url('/customfield/edit_category.php',
                array('handler' => 'core_course\customfield\course_handler', 'itemid' => $data->itemid));
            $data->nocategories = get_string('nocategories', 'core_customfield', (string)$url);
        }

        // Create a new dropdown for types of fields.
        $select = new \single_select($addfieldurl, 'type', $fieldtypes, '', array('' => get_string('choosedots')), 'newfieldform');
        $select->set_label(get_string('createnewcustomfield', 'core_customfield'));
        $data->singleselect = $select->export_for_template($output);

        // Create a new category link.
        $newcategoryurl = new \moodle_url('/customfield/edit_category.php',
            array('handler' => $data->handler, 'itemid' => $data->itemid));
        $data->addcategorybutton = $OUTPUT->single_button($newcategoryurl, get_string('addnewcategory', 'core_customfield'),
                                                          'post', array('class' => 'float-right'));

        return $data;
    }
}
