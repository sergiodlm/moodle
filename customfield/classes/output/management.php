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
class management implements renderable, templatable {

    /**
     * @var handler
     */
    protected $handler;
    /**
     * @var
     */
    protected $categoryid;

    /**
     * management constructor.
     *
     * @param \core_customfield\handler $handler
     */
    public function __construct(\core_customfield\handler $handler) {
        $this->handler = $handler;
    }

    /**
     * @param renderer_base $output
     * @return array|object|\stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = (object) [];

        $fieldtypes = $this->handler->field_types();

        $data->component = $this->handler->get_component();
        $data->area = $this->handler->get_area();
        $data->itemid = $this->handler->get_itemid();
        $data->usescategories = $this->handler->uses_categories();
        $categories = $this->handler->get_fields_definitions();

        $categoriesarray = array();

        foreach ($categories as $category) {

            $categoryarray = array();
            $categoryarray['id'] = $category->get('id');
            $categoryarray['nameeditable'] = $output->render($category->get_inplace_editable(true));

            $categoryarray['fields'] = array();

            foreach ($category->fields() as $field) {
                global $OUTPUT;

                $fieldarray['type'] = $fieldtypes[$field->get('type')];
                $fieldarray['id'] = $field->get('id');
                $fieldarray['name'] = $field->get('name');
                $fieldarray['shortname'] = $field->get('shortname');

                $fieldarray['deletefieldurl'] = (new \moodle_url('/customfield/edit.php', [
                        'delete' => $fieldarray['id'],
                        'type' => $fieldarray['type'],
                        'sesskey' => sesskey()
                ]))->out(false);

                $fieldarray['editfieldurl'] = (new \moodle_url('/customfield/edit.php', [
                        'id' => $fieldarray['id'],
                ]))->out(false);

                $categoryarray['fields'][] = $fieldarray;
            }

            $menu = new \action_menu();
            $menu->set_alignment(\action_menu::BL, \action_menu::BL);
            $menu->set_menu_trigger(get_string('createnewcustomfield', 'core_customfield'));

            $baseaddfieldurl = new \moodle_url('/customfield/edit.php',
                    array('action' => 'editfield', 'categoryid' => $category->get('id')));
            foreach ($fieldtypes as $type => $fieldname) {
                $addfieldurl = new \moodle_url($baseaddfieldurl, array('type' => $type));
                $action = new \action_menu_link_secondary($addfieldurl, null, $fieldname);
                $menu->add($action);
            }
            $menu->attributes['class'] .= ' float-left mr-1';

            $categoryarray['addfieldmenu'] = $output->render($menu);

            $categoriesarray[] = $categoryarray;
        }

        $data->categories = $categoriesarray;

        if (empty($data->categories)) {
            // TODO this page no longer exists
            $url = new \moodle_url('/customfield/edit_category.php',
                array('component' => $data->component, 'area' => $data->area, 'itemid' => $data->itemid));
            $data->nocategories = get_string('nocategories', 'core_customfield', (string)$url);
        }

        return $data;
    }
}
