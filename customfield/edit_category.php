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

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');

$handlerparam = required_param('handler', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);

require_login();

$handler = new $handlerparam(null);

if ($id) {
    $record = $handler->load_category($id);
    $arrayform = ['name' => $record->name(), 'id' => $id];
    $title = get_string('editingcategory', 'core_customfield');
} else {
    $title = get_string('addingnewcategory', 'core_customfield');
    $arrayform = null;
}

$url = new \moodle_url('/customfield/edit_category.php', ['handler' => $handlerparam]);

admin_externalpage_setup('course_customfield');

$mform = $handler->get_category_config_form($handlerparam);

$mform->set_data($arrayform);

// Process Form data.
if ($mform->is_cancelled()) {

    redirect(new \moodle_url($handler->url));

} else if ($data = $mform->get_data()) {

    if (empty($data->id)) {
        // New.
        $category = $handler->new_category($data->name);
    } else {
        // Update.
        $category = $handler->load_category($id);
        $category->name($data->name);
    }

    try {
        $category->save();
        $notification = 'success';
        redirect(new moodle_url($handler->url));
    } catch (\dml_write_exception $exception) {
        $notification = 'error: ' . $exception;
    }
}

$PAGE->set_url($url);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title($title);
$PAGE->navbar->add($title, new \moodle_url($url));

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$render = new \core_renderer($PAGE, 'customfield');

if (isset($notification)) {
    echo $render->notification($notification, $notification);
}

$mform->display();

echo $OUTPUT->footer();
