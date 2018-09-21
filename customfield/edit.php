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
    $record                = \core_customfield\api::get_field($id);
    $type                  = $record->get('type');
    $handler               = \core_customfield\handler::get_handler_for_field($record);
    $arrayform             = (object) $record->to_record();
    $arrayform->configdata = json_decode($arrayform->configdata, true);

    $title = get_string('editingfield', 'core_customfield');
} else {
    $category  = new \core_customfield\category($categoryid);
    $handler   = \core_customfield\handler::get_handler_for_category($category);

    //
    //if ( ! $categoryid ) {
    //    $otherfieldscategory = new \core_customfield\category();
    //    $otherfieldscategory->set('name', get_string('otherfields', 'core_customfield'));
    //    $otherfieldscategory->set('component', $handler->get_component());
    //    $otherfieldscategory->set('area', $handler->get_area());
    //    $otherfieldscategory->set('itemid', $handler->get_item_id());
    //    print_object($otherfieldscategory->save());die;
    //}

    $record    = $handler->new_field($category, $type);
    $arrayform = (object) ['id' => null, 'type' => $type, 'configdata' => ['required' => 0], 'categoryid' => $categoryid];
    $title     = get_string('addingnewcustomfield', 'core_customfield');
}

$url = new \moodle_url('/customfield/edit.php',
                       ['component'  => $handler->get_component(), 'area' => $handler->get_area(),
                        'itemid'     => $handler->get_item_id(),
                        'id'         => $record->get('id'), 'type' => $record->get('type'),
                        'categoryid' => $record->get('categoryid')]);

admin_externalpage_setup('course_customfield');

$categorylist = $handler->categories_list_for_select();
//If no categories are present, we create a new default category.



$args = [ 'categorylist' => $categorylist ];

$mform = $handler->get_field_config_form($record);

$textfieldoptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => 50, 'maxbytes' => 0,
                          'context'   => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true);

file_prepare_standard_editor($arrayform, 'description', $textfieldoptions, $PAGE->context, 'core_customfield',
                             'description', $arrayform->id);

$mform->set_data($arrayform);

// Process Form data.
if ($mform->is_cancelled()) {
    redirect($handler->get_configuration_url());
} else if ($data = $mform->get_data()) {

    if (!empty($data->id)) {
        // Update.
        if (isset($data->description_editor)) {

            $textfieldoptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => 5, 'maxbytes' => 0,
                                 'context'   => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true];

            $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context,
                                                    'core_customfield', 'description', $data->id);
            unset($data->description_editor);
        }
        unset($data->component, $data->area, $data->itemid, $data->submitbutton, $data->descriptiontrust);

        $data->configdata = json_encode( (empty($data->configdata)) ? [] : $data->configdata );
        $record->from_record($data);

        try {
            $record->save();
            redirect($handler->get_configuration_url());
        } catch (\dml_write_exception $exception) {
            core\notification::error(get_string('fieldsavefailed', 'core_customfield'));
        }

    } else {
        if (isset($data->description_editor)) {

            $textfieldoptions = array(
                    'trusttext'             => true,
                    'subdirs'               => true,
                    'maxfiles'              => 5,
                    'maxbytes'              => 0,
                    'context'               => $PAGE->context,
                    'noclean'               => 0,
                    'enable_filemanagement' => true
            );

            // TODO this will not work, $data->id is empty.
            $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context, 'core_customfield',
                                                    'description', $data->id);

            unset($data->description_editor);
        }
        unset($data->component, $data->area, $data->itemid, $data->submitbutton, $data->descriptiontrust);
        $data->configdata = json_encode( (empty($data->configdata)) ? [] : $data->configdata );
        $record->from_record($data);

        try {
            $record->save();
            redirect($handler->get_configuration_url(), get_string('fieldsaved', 'core_customfield'));
        } catch (\dml_write_exception $exception) {
            core\notification::error(get_string('fieldsavefailed', 'core_customfield'));
        }
    }
}

$PAGE->set_url($url);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

$mform->display();

echo $OUTPUT->footer();
