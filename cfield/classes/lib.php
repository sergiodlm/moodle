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

namespace core_cfield;

defined('MOODLE_INTERNAL') || die;

class lib {

    // Edit field form.
    public static function edit_field($args) {
        global $PAGE;
        global $OUTPUT;

        $id = $args['id'];
        $handler = $args['handler'];
        //$action = $args['action'];
        //$itemid = $args['itemid'];
        $type = $args['type'];
        $success = $args['success'];
        $error = $args['error'];

        if ($id) {
            $record = field_factory::load($id);
            $classfieldtype = '\cfield_'. $record->get_type().'\field';
            $categoryid = $record->get_categoryid();
            $configdata = json_decode( $record->get_configdata() );
            $arrayform = (object)[
                    'id'                => $id,
                    'name'              => $record->get_name(),
                    'shortname'         => $record->get_shortname(),
                    'categoryid'        => $record->get_categoryid(),
                    'description'       => $record->get_description(),
                    'descriptionformat' => $record->get_descriptionformat(),
            ];

            // We format configdata fields.
            if($configdata) {
                foreach ($configdata as $a => $b) {
                    $arrayform->configdata[$a] = $b;
                }
            }


        } else {
            $classfieldtype = '\cfield_'.$type.'\field';
            $id = '';
            $arrayform = (object)null;
            $categoryid = null;
        }

        $url = new \moodle_url('/cfield/edit.php');

        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url($url);
        $PAGE->set_pagelayout('report');
        $PAGE->set_title(get_string('customfields', 'core_cfield'));
        $PAGE->navbar->add(get_string('edit'), $url);

        $handler1 = new $handler(null);

        $options = [
                'component' => $handler1->get_component(),
                'area'      => $handler1->get_area(),
                'itemid'    => $handler1->get_item_id()
        ];

        $categorylist = array();
        foreach ( category::list($options) as $category) {
            $categorylist[$category->id] = $category->name;
        }

        $yesnolist = array(
                0 => get_string('no', 'core_cfield'),
                1 => get_string('yes', 'core_cfield')
        );


        $args = array(
                'handler'           => $handler,
                'id'                => $id,
                'classfieldtype'    => $classfieldtype,
                'type'              => $type,
                'categorylist'      => $categorylist,
                'categoryid'        => $categoryid,
                'action'            => 'editfield',
                'yesnolist'         => $yesnolist,
        );

        // Get fields for field type.
        $mform =  $handler1->get_field_config_form(null,$args);

        if ($id) {
             $textfieldoptions = array(
                    'trusttext' => true,
                    'subdirs' => true,
                    'maxfiles' => 50,
                    'maxbytes' => 0,
                    'context' => $PAGE->context,
                    'noclean' => 0,
                    'enable_filemanagement' => true
            );

            file_prepare_standard_editor($arrayform, 'description', $textfieldoptions, $PAGE->context, 'core_cfield', 'description', $arrayform->id);
        }

        $mform->set_data($arrayform);

        // Process Form data.
        if ($mform->is_cancelled()) {
            redirect(new \moodle_url($handler1->url));
        } else if ($data = $mform->get_data()) {

            if (!empty($data->id)) {
                // Update.
                $fielddata = new \stdClass();
                $fielddata->id = $data->id;
                $fielddata->name = $data->name;
                $fielddata->shortname = $data->shortname;
                $fielddata->categoryid = $data->categoryid;
                $fielddata->type = $data->type;//datatype;
                $fielddata->configdata = json_encode ($data->configdata);

                if ( isset($data->description_editor) ) {

                    $textfieldoptions = array('trusttext' => true,
                            'subdirs' => true,
                            'maxfiles' => 5,
                            'maxbytes' => 0,
                            'context' => $PAGE->context,
                            'noclean' => 0,
                            'enable_filemanagement' => true);

                    $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context, 'core_cfield',
                            'description', $data->id);
                    $fielddata->description = $data->description;
                    $fielddata->descriptionformat = $data->descriptionformat;
                }

                $field = new $classfieldtype($fielddata);
                try {
                    $field->save();
                    $url = new \moodle_url($handler1->url, [
                            'handler'   => $handler,
                            'type'      => $type,
                            'success'   => base64_encode('Entry inserted correctly'),
                            'action'    => 'editfield'
                    ]);
                } catch (\dml_write_exception $exception) {
                    $url = new \moodle_url($handler1->url, [
                            'handler'   => $handler,
                            'type'      => $type,
                            'error'     => base64_encode('Error: Duplicate entry'),
                            'action'    => 'editfield'
                    ]);
                }

            } else {
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

                    if ( isset($data->description_editor) ) {

                        $textfieldoptions = array(
                                'trusttext' => true,
                                'subdirs' => true,
                                'maxfiles' => 5,
                                'maxbytes' => 0,
                                'context' => $PAGE->context,
                                'noclean' => 0,
                                'enable_filemanagement' => true
                        );

                        $data = file_postupdate_standard_editor($data, 'description', $textfieldoptions, $PAGE->context, 'core_cfield',
                                'description', $insertid);

                        $savedfield->set_description($data->description);
                        $savedfield->set_descriptionformat($data->descriptionformat);
                        $savedfield->set_id($insertid);
                        $savedfield->save();
                    }

                    $url = new \moodle_url($handler1->url, [
                            'handler'   => $handler,
                            'type'  => $type,
                            'success'   => base64_encode('Entry inserted correctly'),
                            'action'    => 'editfield'
                    ]);
                } catch (\dml_write_exception $exception) {
                    $url = new \moodle_url($handler1->url, [
                            'handler'   => $handler,
                            'type'  => $type,
                            'error'     => base64_encode('Error: Duplicate entry'),
                            'action'    => 'editfield'
                    ]);
                }
            }

            redirect($url);
        }

        echo $OUTPUT->header();
        $render = new \core_renderer($PAGE, 'cfield');

        if ( !empty($success) ) {
            echo $render->notification(base64_decode($_GET['success']), 'success');
        } elseif ( !empty($error) ) {
            echo $render->notification(base64_decode($_GET['error']), 'error');
        }

        $mform->display();

        echo $OUTPUT->footer();
    }


    /**
     * Edit Category form.
     * @param $args array
     * @throws \Horde\Socket\Client\Exception
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function edit_category($args) {
        global $PAGE;
        global $OUTPUT;

        $id = $args['id'];
        $handler = $args['handler'];
        $action = $args['action'];

        if ($id) {
            $record = category::load($id);

            $arrayform = [
                    'name' => $record->get_name(),
            ];

        }else {
            $arrayform = null;
        }

        $url = new \moodle_url('/cfield/edit.php');

        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url($url);
        $PAGE->set_pagelayout('report');
        $PAGE->set_title(get_string('customfields', 'core_cfield'));
        $PAGE->navbar->add(get_string('edit'), new \moodle_url($url));

        $handler1 = new $handler(null);

        $args = [
                'handler'   => $handler,
                'id'        => $id,
                'action'    => $action
        ];

        $mform =  $handler1->get_category_config_form(null,$args);

        $mform->set_data($arrayform);

        // Process Form data.
        if ($mform->is_cancelled()) {
            redirect(new \moodle_url($handler1->url));
        } else if ($data = $mform->get_data()) {

            if (!empty($data->id)) {
                // Update.
                $category = category::load($id);
                $category->set_name($data->name);
            } else {
                // New.
                $categorydata = new \stdClass();
                $categorydata->name = $data->name;
                $categorydata->component = $handler1->get_component();
                $categorydata->area = $handler1->get_area();
                $categorydata->itemid = $handler1->get_item_id();
                $category = new category($categorydata);
            }

            try {
                $category->save();
                $url = new \moodle_url($handler1->url, [
                        'handler'   => $handler,
                        'success'   => base64_encode('Entry inserted correctly'),
                        'action'    => $action
                ]);
            } catch (\dml_write_exception $exception) {
                $url = new \moodle_url($handler1->url, [
                        'handler'   => $handler,
                        'error'     => base64_encode('Error: Duplicate entry'),
                        'action'    => $action
                ]);
            }
            redirect($url);
        }

        echo $OUTPUT->header();
        $render = new \core_renderer($PAGE, 'cfield');
        if ( !empty($success) ) {
            echo $render->notification(base64_decode($_GET['success']), 'success');
        } elseif ( !empty($error) ) {
            echo $render->notification(base64_decode($_GET['error']), 'error');
        }

        $mform->display();

        echo $OUTPUT->footer();
    }
}
