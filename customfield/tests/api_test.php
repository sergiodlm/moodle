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
 * Tests for class core_course_category.
 *
 * @package    core_customfield
 * @category   phpunit
 * @copyright  Toni Barbera <toni@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use advanced_testcase;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * Functional test for class core_customfield_api
 */
class core_customfield_api_testcase extends advanced_testcase {
    /**
     * setUp.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_get_fields_definitions() {
        // Create the categories.
        $options = [
                'component' => 'core_course',
                'area'      => 'course',
                'itemid'    => 0,
                'contextid' => 1
        ];

        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = $options['component'];
        $categorydata->area      = $options['area'];
        $categorydata->itemid    = $options['itemid'];
        $categorydata->contextid = $options['contextid'];
        $category0               = new category(0, $categorydata);
        $category0->save();

        $categorydata->name = 'bbbb';
        $category1          = new category(0, $categorydata);
        $category1->save();

        $categorydata->name = 'cccc';
        $category2          = new category(0, $categorydata);
        $category2->save();

        $categorydata->name = 'dddd';
        $category3          = new category(0, $categorydata);
        $category3->save();

        $categorydata->name = 'eeee';
        $category4          = new category(0, $categorydata);
        $category4->save();

        $categorydata->name = 'ffff';
        $category5          = new category(0, $categorydata);
        $category5->save();

        $this->assertCount(0, api::get_fields_definitions('xxx', $options['area'], $options['itemid']));
        $this->assertCount(0, api::get_fields_definitions($options['component'], 'xxx', $options['itemid']));
        $this->assertCount(0, api::get_fields_definitions($options['component'], $options['area'], 42));
        $this->assertCount(6, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category0->delete();
        $this->assertCount(5, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category1->delete();
        $this->assertCount(4, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category2->delete();
        $this->assertCount(3, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category3->delete();
        $this->assertCount(2, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category4->delete();
        $this->assertCount(1, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
        $category5->delete();
        $this->assertCount(0, api::get_fields_definitions($options['component'], $options['area'], $options['itemid']));
    }

    public function test_get_field() {
        // Create the category.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;
        $category0               = new category(0, $categorydata);
        $category0->save();

        // Add fields to this category.
        $fielddata                = new stdClass();
        $fielddata->nameshortname = 'aaaa';
        $fielddata->categoryid    = $category0->get('id');
        $fielddata->configdata    = "{\"required\":\"0\",\"uniquevalues\":\"0\",\"locked\":\"0\",\"visibility\":\"0\",
                                    \"defaultvalue\":\"\",\"displaysize\":0,\"maxlength\":0,\"ispassword\":\"0\",
                                    \"link\":\"\",\"linktarget\":\"\"}";

        $field0 = new \customfield_text\field();
        $field0->set('name', $fielddata->nameshortname);
        $field0->set('shortname', $fielddata->nameshortname);
        $field0->set('categoryid', $category0->get('id'));
        $field0->set('type', 'text');
        $field0->set('configdata', $fielddata->configdata);
        $field0->set_category($category0);
        $field0->save();
        $id0 = $field0->get('id');

        $fielddata->nameshortname = 'bbbb';
        $field1                   = new \customfield_text\field();
        $field1->set('name', $fielddata->nameshortname);
        $field1->set('shortname', $fielddata->nameshortname);
        $field1->set('categoryid', $category0->get('id'));
        $field1->set('type', 'text');
        $field1->set('configdata', $fielddata->configdata);
        $field1->set_category($category0);
        $field1->save();

        $fielddata->nameshortname = 'cccc';
        $field2                   = new \customfield_text\field();
        $field2->set('name', $fielddata->nameshortname);
        $field2->set('shortname', $fielddata->nameshortname);
        $field2->set('categoryid', $category0->get('id'));
        $field2->set('type', 'text');
        $field2->set('configdata', $fielddata->configdata);
        $field2->set_category($category0);
        $field2->save();

        $fielddata->nameshortname = 'dddd';
        $field3                   = new \customfield_text\field();
        $field3->set('name', $fielddata->nameshortname);
        $field3->set('shortname', $fielddata->nameshortname);
        $field3->set('categoryid', $category0->get('id'));
        $field3->set('type', 'text');
        $field3->set('configdata', $fielddata->configdata);
        $field3->set_category($category0);
        $field3->save();

        $fielddata->nameshortname = 'eeee';
        $field4                   = new \customfield_text\field();
        $field4->set('name', $fielddata->nameshortname);
        $field4->set('shortname', $fielddata->nameshortname);
        $field4->set('categoryid', $category0->get('id'));
        $field4->set('type', 'text');
        $field4->set('configdata', $fielddata->configdata);
        $field4->set_category($category0);
        $field4->save();

        $fielddata->nameshortname = 'ffff';
        $field5                   = new \customfield_date\field();
        $field5->set('name', $fielddata->nameshortname);
        $field5->set('shortname', $fielddata->nameshortname);
        $field5->set('categoryid', $category0->get('id'));
        $field5->set('type', 'date');
        $field5->set('configdata', $fielddata->configdata);
        $field5->set_category($category0);
        $field5->save();

        $this->assertInstanceOf('customfield_text\field', api::get_field($field0->get('id')));
        $this->assertInstanceOf('customfield_text\field', api::get_field($field1->get('id')));
        $this->assertInstanceOf('customfield_text\field', api::get_field($field2->get('id')));
        $this->assertInstanceOf('customfield_text\field', api::get_field($field3->get('id')));
        $this->assertInstanceOf('customfield_text\field', api::get_field($field4->get('id')));
        $this->assertInstanceOf('customfield_date\field', api::get_field($field5->get('id')));
        $this->assertNotInstanceOf('customfield_text\field', api::get_field($field5->get('id')));
        $this->assertNotInstanceOf('customfield_checkbox\field', api::get_field($field0->get('id')));
    }

    public function test_load_data() {

    }

    public function test_get_fields_with_data() {

    }

    public function test_field_types() {

    }

    public function test_save_field() {

    }

    public function test_save_category() {

    }
}
