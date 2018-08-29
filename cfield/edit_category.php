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
 * @copyright 2018 Daniel Neis Araujo <danielneis@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../config.php');

$handlerparam = required_param('handler', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);

$handler = new $handlerparam(null);

if ($id) {
    $record = $handler->load_category($id);
    $arrayform = ['name' => $record->get_name(), 'id' => $id];
} else {
    $arrayform = null;
}

$url = new \moodle_url('/cfield/edit_category.php', ['handler' => $handlerparam]);

$PAGE->set_context(\context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('customfields', 'core_cfield'));
$PAGE->navbar->add(get_string('edit'), new \moodle_url($url));

$mform = $handler->get_category_config_form($handlerparam);

$mform->set_data($arrayform);

// Process Form data.
if ($mform->is_cancelled()) {

    redirect($url);

} else if ($data = $mform->get_data()) {

    if (!empty($data->id)) {
        // Update.
        $category = $handler->load_category($id);
        $category->set_name($data->name);
    } else {
        // New.
        $category = $handler->new_category($data->name);
    }

    try {
        $category->save();
        $notification = 'success';
        redirect(new moodle_url($handler->url));
    } catch (\dml_write_exception $exception) {
        $notification = 'error';
    }
}

echo $OUTPUT->header();
$render = new \core_renderer($PAGE, 'cfield');

if (isset($notification)) {
    echo $render->notification($notification, $notification);
}

$mform->display();

echo $OUTPUT->footer();
