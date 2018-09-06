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

define('OTHERFIELDSNAME', 'Other Fields'); //Need becomes from lang file.
define('OTHERFIELDSSHORTNAME', 'otherfields'); //Need becomes from lang file.


$handlerparam = required_param('handler', PARAM_RAW);
$itemid = optional_param('itemid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$type = optional_param('type', null, PARAM_NOTAGS);

$handler = new $handlerparam(null);

if ($id) {
    $record = \core_customfield\field_factory::load($id);
    $classfieldtype = '\customfield_'. $record->get_type().'\field';
    $configdata = json_decode( $record->get_configdata() );
    $arrayform = (object)[
            'id'                => $id,
            'name'              => $record->get_name(),
            'shortname'         => $record->get_shortname(),
            'type'              => $record->get_type(),
            'categoryid'        => $record->get_categoryid(),
            'description'       => $record->get_description(),
            'descriptionformat' => $record->get_descriptionformat(),
    ];

    // We format configdata fields.
    if ($configdata) {
        foreach ($configdata as $a => $b) {
            $arrayform->configdata[$a] = $b;
        }
    }

} else {
    $classfieldtype = '\customfield_'.$type.'\field';
    $arrayform = (object)null;
    $arrayform->type = $type;
    $arrayform->configdata = ['required' => 0];
}

$url = new \moodle_url('/customfield/edit.php', ['handler' => $handlerparam]);

$PAGE->set_context(\context_system::instance());
$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('customfields', 'core_customfield'));
$PAGE->navbar->add(get_string('edit'), $url);

//$handler = new $handlerparam(null);

$categorylist = array();
foreach ($handler->categories_list() as $category) {
    $categorylist[$category->id()] = $category->name();
}

$args = ['handler' => $handlerparam, 'classfieldtype' => $classfieldtype, 'categorylist' => $categorylist];

// Get fields for field type.
$mform = $handler->get_field_config_form($args);

if ($id) {
    $textfieldoptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => 50, 'maxbytes' => 0,
                              'context' => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true);

    file_prepare_standard_editor($arrayform, 'description', $textfieldoptions, $PAGE->context, 'core_customfield',
                                 'description', $arrayform->id);
}

$mform->set_data($arrayform);

// Process Form data.
if ($mform->is_cancelled()) {
    redirect(new \moodle_url($handler->url));
} else if ($data = $mform->get_data()) {

    if (!empty($data->id)) {
        // Update.
        $fielddata = new \stdClass();
        $fielddata->id = $data->id;
        $fielddata->name = $data->name;
        $fielddata->shortname = $data->shortname;
        $fielddata->categoryid = $data->categoryid;
        $fielddata->type = $data->type;
        $fielddata->configdata = json_encode($data->configdata);

        if ( isset($data->description_editor) ) {

            $textfieldoptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => 5, 'maxbytes' => 0,
                                 'context' => $PAGE->context, 'noclean' => 0, 'enable_filemanagement' => true];

            $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context,
                                                    'core_customfield', 'description', $data->id);

            $fielddata->description = $data->description;
            $fielddata->descriptionformat = $data->descriptionformat;
        }

        $field = new $classfieldtype($fielddata);
        try {
            $field->save();
            redirect(new moodle_url($handler->url));
        } catch (\dml_write_exception $exception) {
            $notification = 'error';
        }

    } else {

    	if (empty($data->categoryid)) {
			$defaultcategorydata            = new \stdClass();
			$defaultcategorydata->name      = OTHERFIELDSNAME;
			$defaultcategorydata->shortname = OTHERFIELDSSHORTNAME;
			$defaultcategorydata->area      = $handler->get_area();
			$defaultcategorydata->component = $handler->get_component();
			$defaultcategory                = new \core_customfield\category($defaultcategorydata);
			$defaultcategory->save();

			$data->categoryid = $defaultcategory->get_id();
		}

        // New.
        $fielddata = new \stdClass();
        $fielddata->name = $data->name;
        $fielddata->shortname = $data->shortname;
        $fielddata->categoryid = $data->categoryid;
        $fielddata->type = $type;

        $field = new $classfieldtype($fielddata);
        try {
            $savedfield = $field->save();
            $insertid = $savedfield->get_id();

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

                $savedfield->set_description($data->description);
                $savedfield->set_descriptionformat($data->descriptionformat);
                $savedfield->set_id($insertid);
                $savedfield->save();
            }
            $notification = 'success';
            redirect(new moodle_url($handler->url));
        } catch (\dml_write_exception $exception) {
            $notification = 'error';
        }
    }
}

echo $OUTPUT->header();

if (isset($notification)) {
    $renderer = new \core_renderer($PAGE, 'customfield');
    echo $renderer->notification($notification, $notification);
}

$mform->display();

echo $OUTPUT->footer();
