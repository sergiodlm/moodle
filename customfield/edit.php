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

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id         = optional_param('id', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$type       = optional_param('type', null, PARAM_COMPONENT);

require_login();

if ($id) {
    $record = \core_customfield\api::get_field($id);
    $handler = \core_customfield\handler::get_handler_for_field($record);
    $title = get_string('editingfield', 'core_customfield');
} else {
    $category = new \core_customfield\category($categoryid);
    $handler = \core_customfield\handler::get_handler_for_category($category);
    $record = $handler->new_field($category, $type);
    $title = get_string('addingnewcustomfield', 'core_customfield');
}

$url = new \moodle_url('/customfield/edit.php',
                       ['component'  => $handler->get_component(), 'area' => $handler->get_area(),
                        'itemid'     => $handler->get_item_id(),
                        'id'         => $record->get('id'), 'type' => $record->get('type'),
                        'categoryid' => $record->get('categoryid')]);

$PAGE->set_url($url);
if (!$handler->can_configure()) {
    print_error('nopermissionconfigure', 'core_customfield');
}
$PAGE->set_context(context_system::instance());

$mform = $handler->get_field_config_form($record);
// Process Form data.
if ($mform->is_cancelled()) {
    redirect($handler->get_configuration_url());
} else if ($data = $mform->get_data()) {
    $handler->save_field($record, $data);
    redirect($handler->get_configuration_url());
}

$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$mform->display();

echo $OUTPUT->footer();
