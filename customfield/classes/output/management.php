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

        // TODO: load field types from database or files and get their names from a callback or predefined string key.
        $options = [
                'text' => 'Text Input',
                'textarea' => 'Text Area',
                'select' => 'Dropdown Menu',
                'checkbox' => 'Checkbox',
                'date' => 'Date Time'
        ];

        $data->customfield = get_string('customfield', 'core_customfield');
        $data->action = get_string('action', 'core_customfield');
        $data->shortname = get_string('shortname', 'core_customfield');
        $data->type = get_string('type', 'core_customfield');
        $data->handler = get_class($this->handler);
        $data->link = new \moodle_url('/customfield/edit.php', array('handler' => $data->handler, 'action' => 'editfield'));

        $categories = $this->handler->get_fields_definitions();

        $deleteicon = $OUTPUT->pix_icon('t/delete', get_string('delete'));
        $editicon = $OUTPUT->pix_icon('t/edit', get_string('edit'));
        $upicon = $OUTPUT->pix_icon('t/up', get_string('moveup'));
        $downicon = $OUTPUT->pix_icon('t/down', get_string('movedown'));
        $spacericon = $OUTPUT->pix_icon('spacer', '');

        $categoriesarray = array();

        foreach ($categories as $category) {

            $categoryarray = array();
            $categoryarray['id'] = $category->id();
            $categoryarray['name'] = $category->name();
            $categoryarray['customfield'] = get_string('customfield', 'core_customfield');
            $categoryarray['action'] = get_string('action', 'core_customfield');
            $categoryarray['deleteicon'] = $deleteicon;
            $categoryarray['editicon'] = $editicon;

            // Move up and down categories.
            $sortorder = $category->sortorder();
            if ($sortorder < $category->get_count_categories() - 1) {
                $categoryarray['upiconcategory'] = $upicon;
            } else {
                $categoryarray['upiconcategory'] = '';
            }
            if ($sortorder > 0) {
                $categoryarray['downiconcategory'] = $downicon;
            } else {
                $categoryarray['downiconcategory'] = '';
            }

            $categoryarray['deletecategoryurl'] = (string) new \moodle_url('/customfield/edit_category.php', [
                    'deletecategory' => $categoryarray['id'],
                    'handler' => $data->handler,
                    'sesskey' => sesskey()
            ]);

            $categoryarray['editcategoryurl'] = (string) new \moodle_url('/customfield/edit_category.php', [
                    'id' => $categoryarray['id'], 'handler' => $data->handler,
            ]);

            foreach ($category->fields() as $field) {
                global $OUTPUT;

                $fieldarray['type'] = $options[$field->type()];
                $fieldarray['id'] = $field->id();
                $fieldarray['name'] = $field->name();
                $fieldarray['shortname'] = $field->shortname();
                $fieldarray['deleteicon'] = $deleteicon;
                $fieldarray['editicon'] = $editicon;

                // Move up and down fields.
                $sortorder = $field->sortorder();
                if ($sortorder < $field->get_count_fields() - 1) {
                    $fieldarray['upiconfield'] = $upicon;
                } else {
                    $fieldarray['upiconfield'] = $spacericon;
                }
                if ($sortorder > 0) {
                    $fieldarray['downiconfield'] = $downicon;
                } else {
                    $fieldarray['downiconfield'] = $spacericon;
                }

                $fieldarray['deletefieldurl'] = (string) new \moodle_url('/customfield/edit.php', [
                        'delete' => $fieldarray['id'],
                        'handler' => $data->handler,
                        'type' => $fieldarray['type'],
                        'sesskey' => sesskey()
                ]);

                $fieldarray['editfieldurl'] = (string) new \moodle_url('/customfield/edit.php', [
                        'id' => $fieldarray['id'],
                        'handler' => $data->handler,
                        'type' => $fieldarray['type'],
                ]);

                $categoryarray['fields'][] = $fieldarray;
                //$fieldexporter = new \core_customfield\list_exporter($fieldarray);
                //$categoryarray['fields'][] = $fieldexporter->export($output);
            }
            $categoriesarray[] = $categoryarray;
        }

        $data->categories = $categoriesarray;

        // Create a new dropdown for types of fields.
        $select = new \single_select($data->link, 'type', $options, '', array('' => get_string('choosedots')), 'newfieldform');
        $select->set_label(get_string('createnewcustomfield', 'core_customfield'));
        $data->singleselect = $select->export_for_template($output);

        // Create a new category link.
        $newcategoryurl = new \moodle_url('/customfield/edit_category.php', array('handler' => $data->handler));
        $data->addcategorybutton = $OUTPUT->single_button($newcategoryurl, get_string('addnewcategory', 'core_customfield'),
                                                          'post', array('class' => 'float-right'));

        return $data;
    }
}
