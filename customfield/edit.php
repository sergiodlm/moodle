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
require_once($CFG->libdir.'/adminlib.php');

$handlerparam = required_param('handler', PARAM_RAW);
$itemid = optional_param('itemid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type', null, PARAM_NOTAGS);

require_login();

$handler = new $handlerparam(null);

if ($id) {
    $record = \core_customfield\field_factory::load($id);
    $classfieldtype = '\customfield_'. $record->type().'\field';
    $configdata = json_decode( $record->configdata() );
    // TODO: find a better approach to this!
    $arrayform = (object)[
            'id'                => $id,
            'name'              => $record->name(),
            'shortname'         => $record->shortname(),
            'type'              => $record->type(),
            'categoryid'        => $record->categoryid(),
            'required'          => $record->required(),
            'locked'            => $record->locked(),
            'uniquevalues'      => $record->uniquevalues(),
            'visibility'        => $record->visibility(),
            'description'       => $record->description(),
            'descriptionformat' => $record->descriptionformat(),
    ];

    // We format configdata fields.
    if ($configdata) {
        foreach ($configdata as $a => $b) {
            $arrayform->configdata[$a] = $b;
        }
    }

    $title = get_string('editingfield', 'core_customfield');

} else {
    $classfieldtype = '\customfield_'.$type.'\field';
    $arrayform = (object)null;
    $arrayform->type = $type;
    $arrayform->id = null;
    $arrayform->configdata = ['required' => 0];
    $title = get_string('addingnewcustomfield', 'core_customfield');
}

$url = new \moodle_url('/customfield/edit.php', ['handler' => $handlerparam]);

admin_externalpage_setup('course_customfield');

$categorylist = $handler->categories_list_for_select();
$args = ['handler' => $handlerparam, 'classfieldtype' => $classfieldtype, 'categorylist' => $categorylist];

$mform = $handler->get_field_config_form($args);

$textfieldoptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => 50, 'maxbytes' => 0,
                          'context' => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true);

file_prepare_standard_editor($arrayform, 'description', $textfieldoptions, $PAGE->context, 'core_customfield',
                             'description', $arrayform->id);

$mform->set_data($arrayform);

// Process Form data.
if ($mform->is_cancelled()) {
    redirect(new \moodle_url($handler->url));
} else if ($data = $mform->get_data()) {

    if (!empty($data->id)) {
        // Update.
        $data->configdata = json_encode($data->configdata);
        if ( isset($data->description_editor) ) {

            $textfieldoptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => 5, 'maxbytes' => 0,
                                 'context' => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true];

            $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context,
                                                    'core_customfield', 'description', $data->id);

            $data->description = $data->description;
            $data->descriptionformat = $data->descriptionformat;
            unset($data->description_editor);
        }
        unset($data->handler, $data->submitbutton, $data->descriptiontrust);

        $field = new $classfieldtype($data->id, $data);
        try {
            $field->save();
            redirect(new moodle_url($handler->url));
        } catch (\dml_write_exception $exception) {
            $notification = $exception->error;
        }

    } else {

    	if (empty($data->categoryid)) {
            $defaultcategorydata            = new \stdClass();
            $defaultcategorydata->name      = get_string('otherfields', 'core_customfield');
            $defaultcategorydata->area      = $handler->get_area();
            $defaultcategorydata->component = $handler->get_component();

            $defaultcategory                = new \core_customfield\category(0, $defaultcategorydata);
            $defaultcategory->save();

            $data->categoryid = $defaultcategory->id();
        }
        // New.
        $fielddata = new \stdClass();
        $fielddata->name = $data->name;
        $fielddata->shortname = $data->shortname;
        $fielddata->categoryid = $data->categoryid;
        $fielddata->type = $type;

        $field = new $classfieldtype(0, $fielddata);
        $field->save();

        try {
            $field->save();
            $insertid = $field->id();

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

                $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context, 'core_customfield',
                                                        'description', $insertid);

                $field->description($data->description);
                $field->descriptionformat($data->descriptionformat);
                $field->save();
            }
            $notification = 'success';
            redirect(new moodle_url($handler->url));
        } catch (\dml_write_exception $exception) {
            $notification = 'error';
        }
    }
}

$PAGE->set_url($url);
$PAGE->set_heading(get_site()->fullname);
$PAGE->set_title($title);
$PAGE->navbar->add($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if (isset($notification)) {
    $renderer = new \core_renderer($PAGE, 'customfield');
    echo $renderer->notification($notification, $notification);
}

$mform->display();

echo $OUTPUT->footer();
