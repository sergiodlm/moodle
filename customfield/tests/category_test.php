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
 * Functional test for class core_customfield_category
 */
class core_customfield_category_testcase extends advanced_testcase {
    /**
     * setUp.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * @throws \coding_exception
     */
    public function test_create_category_and_correctlly_reorder() {

        // Create the category.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;

        $category0 = new category(0, $categorydata);
        $category0->save();

        // Initially confirm that base data was inserted correctly.
        $this->assertSame($category0->get('name'), $categorydata->name);
        $this->assertSame($category0->get('description'), null);
        $this->assertSame($category0->get('descriptionformat'), '0');
        $this->assertSame($category0->get('component'), $categorydata->component);
        $this->assertSame($category0->get('area'), $categorydata->area);
        $this->assertSame($category0->get('itemid'), $categorydata->itemid);
        $this->assertSame($category0->get('contextid'), $categorydata->contextid);
        $this->assertSame($category0->get('sortorder'), -1);

        // Creating 2nd category and check if sortorder is correct.
        $categorydata->name = 'bbbb';

        $category1 = new category(0, $categorydata);
        $category1->save();

        // Initially confirm that base data was inserted correctly.
        $this->assertSame($category1->get('name'), $categorydata->name);
        $this->assertSame($category1->get('description'), null);
        $this->assertSame($category1->get('descriptionformat'), '0');
        $this->assertSame($category1->get('component'), $categorydata->component);
        $this->assertSame($category1->get('area'), $categorydata->area);
        $this->assertSame($category1->get('itemid'), $categorydata->itemid);
        $this->assertSame($category1->get('contextid'), $categorydata->contextid);
        $this->assertSame($category1->get('sortorder'), -1);

        $id0 = $category0->get('id');
        $id1 = $category1->get('id');

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);

        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);

        // Creating 3rd category and check if sortorder is correct.
        $categorydata->name = 'cccc';

        $category2 = new category(0, $categorydata);
        $category2->save();

        // Initially confirm that base data was inserted correctly.
        $this->assertSame($category2->get('name'), $categorydata->name);
        $this->assertSame($category2->get('description'), null);
        $this->assertSame($category2->get('descriptionformat'), '0');
        $this->assertSame($category2->get('component'), $categorydata->component);
        $this->assertSame($category2->get('area'), $categorydata->area);
        $this->assertSame($category2->get('itemid'), $categorydata->itemid);
        $this->assertSame($category2->get('contextid'), $categorydata->contextid);
        $this->assertSame($category2->get('sortorder'), -1);

        $id2 = $category2->get('id');

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);

        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);

        // Creating 4th category and check if sortorder is correct.
        $categorydata->name = 'dddd';

        $category3 = new category(0, $categorydata);
        $category3->save();

        // Initially confirm that base data was inserted correctly.
        $this->assertSame($category3->get('name'), $categorydata->name);
        $this->assertSame($category3->get('description'), null);
        $this->assertSame($category3->get('descriptionformat'), '0');
        $this->assertSame($category3->get('component'), $categorydata->component);
        $this->assertSame($category3->get('area'), $categorydata->area);
        $this->assertSame($category3->get('itemid'), $categorydata->itemid);
        $this->assertSame($category3->get('contextid'), $categorydata->contextid);
        $this->assertSame($category3->get('sortorder'), -1);

        $id3 = $category3->get('id');

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);

        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
    }

    /**
     * @throws \coding_exception
     */
    public function test_create_category_and_rename() {
        // Create the category.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;

        $category0 = new category(0, $categorydata);
        $category0->save();

        // Initially confirm that base data was inserted correctly.
        $this->assertSame($category0->get('name'), $categorydata->name);
        $this->assertSame($category0->get('description'), null);
        $this->assertSame($category0->get('descriptionformat'), '0');
        $this->assertSame($category0->get('component'), $categorydata->component);
        $this->assertSame($category0->get('area'), $categorydata->area);
        $this->assertSame($category0->get('itemid'), $categorydata->itemid);
        $this->assertSame($category0->get('contextid'), $categorydata->contextid);
        $this->assertSame($category0->get('sortorder'), -1);

        // Checking new name are correct updated.
        $newname = 'bbbb';
        $category0->set('name', $newname);
        $this->assertSame($category0->get('name'), $newname);

        // Checking new name are correct updated after save.
        $category0->save();
        $id = $category0->get('id');

        $category0 = new category($id);
        $this->assertSame($category0->get('name'), $newname);
    }

    /**
     * @throws \coding_exception
     */
    public function test_create_category_and_delete() {
        // Create the category.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;

        $category0 = new category(0, $categorydata);
        $category0->save();
        $id0 = $category0->get('id');

        $categorydata->name = 'bbbb';
        $category1          = new category(0, $categorydata);
        $category1->save();
        $id1 = $category1->get('id');

        $categorydata->name = 'cccc';
        $category2          = new category(0, $categorydata);
        $category2->save();
        $id2 = $category2->get('id');

        // Confirm that exist in the database.
        $this->assertTrue(category::record_exists($id0));

        //Delete and confirm that is deleted.
        $category0->delete();
        $this->assertFalse(category::record_exists($id0));

        // Confirm correct order after delete.
        // Check order after re-fetch.
        $category1 = new category($id1);
        $category2 = new category($id2);

        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_create_categories_and_move_it_as_drag_and_drop() {
        // Create the categories.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;
        $category0               = new category(0, $categorydata);
        $category0->save();
        $id0 = $category0->get('id');

        $categorydata->name = 'bbbb';
        $category1          = new category(0, $categorydata);
        $category1->save();
        $id1 = $category1->get('id');

        $categorydata->name = 'cccc';
        $category2          = new category(0, $categorydata);
        $category2->save();
        $id2 = $category2->get('id');

        $categorydata->name = 'dddd';
        $category3          = new category(0, $categorydata);
        $category3->save();
        $id3 = $category3->get('id');

        $categorydata->name = 'eeee';
        $category4          = new category(0, $categorydata);
        $category4->save();
        $id4 = $category4->get('id');

        $categorydata->name = 'ffff';
        $category5          = new category(0, $categorydata);
        $category5->save();
        $id5 = $category5->get('id');

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);

        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move up 1 position.
        api::move_category(new category($id3), $id2);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 3);
        $this->assertSame((int) $category3->get('sortorder'), 2);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move down 1 position.
        api::move_category(new category($id2), $id3);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move up 2 positions.
        api::move_category(new category($id4), $id2);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 3);
        $this->assertSame((int) $category3->get('sortorder'), 4);
        $this->assertSame((int) $category4->get('sortorder'), 2);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move down 2 positions.
        api::move_category(new category($id4), $id5);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move up 3 positions.
        api::move_category(new category($id4), $id1);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 2);
        $this->assertSame((int) $category2->get('sortorder'), 3);
        $this->assertSame((int) $category3->get('sortorder'), 4);
        $this->assertSame((int) $category4->get('sortorder'), 1);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Move down 3 positions.
        api::move_category(new category($id4), $id5);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        //Move to the end of the list.
        api::move_category(new category($id2), 0);
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);
        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 5);
        $this->assertSame((int) $category3->get('sortorder'), 2);
        $this->assertSame((int) $category4->get('sortorder'), 3);
        $this->assertSame((int) $category5->get('sortorder'), 4);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_categories_list_reorder() {
        // Create the categories.
        $categorydata            = new stdClass();
        $categorydata->name      = 'aaaa';
        $categorydata->component = 'core_course';
        $categorydata->area      = 'course';
        $categorydata->itemid    = 0;
        $categorydata->contextid = 1;
        $category0               = new category(0, $categorydata);
        $category0->save();
        $id0 = $category0->get('id');

        $categorydata->name = 'bbbb';
        $category1          = new category(0, $categorydata);
        $category1->save();
        $id1 = $category1->get('id');

        $categorydata->name = 'cccc';
        $category2          = new category(0, $categorydata);
        $category2->save();
        $id2 = $category2->get('id');

        $categorydata->name = 'dddd';
        $category3          = new category(0, $categorydata);
        $category3->save();
        $id3 = $category3->get('id');

        $categorydata->name = 'eeee';
        $category4          = new category(0, $categorydata);
        $category4->save();
        $id4 = $category4->get('id');

        $categorydata->name = 'ffff';
        $category5          = new category(0, $categorydata);
        $category5->save();
        $id5 = $category5->get('id');

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);

        $this->assertSame((int) $category0->get('sortorder'), 0);
        $this->assertSame((int) $category1->get('sortorder'), 1);
        $this->assertSame((int) $category2->get('sortorder'), 2);
        $this->assertSame((int) $category3->get('sortorder'), 3);
        $this->assertSame((int) $category4->get('sortorder'), 4);
        $this->assertSame((int) $category5->get('sortorder'), 5);

        // Wrong sortorder values forced.
        $category0->set('sortorder', 101);
        $category0->save();
        $category1->set('sortorder', 42);
        $category1->save();
        $category2->set('sortorder', 3);
        $category2->save();
        $category3->set('sortorder', 14);
        $category3->save();
        $category4->set('sortorder', 15);
        $category4->save();
        $category5->set('sortorder', 92);
        $category5->save();

        // Check order after re-fetch.
        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);

        $this->assertSame((int) $category0->get('sortorder'), 101);
        $this->assertSame((int) $category1->get('sortorder'), 42);
        $this->assertSame((int) $category2->get('sortorder'), 3);
        $this->assertSame((int) $category3->get('sortorder'), 14);
        $this->assertSame((int) $category4->get('sortorder'), 15);
        $this->assertSame((int) $category5->get('sortorder'), 92);

        // Force reorder, reload and check status.
        //api::reorder_categories($categorydata->component, $categorydata->area, $categorydata->itemid);
        api::move_category($category0, 0);

        $category0 = new category($id0);
        $category1 = new category($id1);
        $category2 = new category($id2);
        $category3 = new category($id3);
        $category4 = new category($id4);
        $category5 = new category($id5);

        $this->assertSame((int) $category2->get('sortorder'), 0);
        $this->assertSame((int) $category3->get('sortorder'), 1);
        $this->assertSame((int) $category4->get('sortorder'), 2);
        $this->assertSame((int) $category1->get('sortorder'), 3);
        $this->assertSame((int) $category5->get('sortorder'), 4);
        $this->assertSame((int) $category0->get('sortorder'), 5);
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function test_categories_before_delete() {
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
        $fielddata->type          = 'text';
        $fielddata->categoryid    = $category0->get('id');
        $fielddata->configdata    = "{\"required\":\"0\",\"uniquevalues\":\"0\",\"locked\":\"0\",\"visibility\":\"0\",
                                    \"defaultvalue\":\"\",\"displaysize\":0,\"maxlength\":0,\"ispassword\":\"0\",
                                    \"link\":\"\",\"linktarget\":\"\"}";

        $field0 = new \customfield_text\field();
        $field0->set('name', $fielddata->nameshortname);
        $field0->set('shortname', $fielddata->nameshortname);
        $field0->set('categoryid', $category0->get('id'));
        $field0->set('type', $fielddata->type);
        $field0->set('configdata', $fielddata->configdata);
        $field0->set_category($category0);
        $field0->save();

        $fielddata->nameshortname = 'bbbb';
        $field1                   = new \customfield_text\field();
        $field1->set('name', $fielddata->nameshortname);
        $field1->set('shortname', $fielddata->nameshortname);
        $field1->set('categoryid', $category0->get('id'));
        $field1->set('type', $fielddata->type);
        $field1->set('configdata', $fielddata->configdata);
        $field1->set_category($category0);
        $field1->save();

        $fielddata->nameshortname = 'cccc';
        $field2                   = new \customfield_text\field();
        $field2->set('name', $fielddata->nameshortname);
        $field2->set('shortname', $fielddata->nameshortname);
        $field2->set('categoryid', $category0->get('id'));
        $field2->set('type', $fielddata->type);
        $field2->set('configdata', $fielddata->configdata);
        $field2->set_category($category0);
        $field2->save();

        $fielddata->nameshortname = 'dddd';
        $field3                   = new \customfield_text\field();
        $field3->set('name', $fielddata->nameshortname);
        $field3->set('shortname', $fielddata->nameshortname);
        $field3->set('categoryid', $category0->get('id'));
        $field3->set('type', $fielddata->type);
        $field3->set('configdata', $fielddata->configdata);
        $field3->set_category($category0);
        $field3->save();

        $fielddata->nameshortname = 'eeee';
        $field4                   = new \customfield_text\field();
        $field4->set('name', $fielddata->nameshortname);
        $field4->set('shortname', $fielddata->nameshortname);
        $field4->set('categoryid', $category0->get('id'));
        $field4->set('type', $fielddata->type);
        $field4->set('configdata', $fielddata->configdata);
        $field4->set_category($category0);
        $field4->save();

        $fielddata->nameshortname = 'ffff';
        $field5                   = new \customfield_text\field();
        $field5->set('name', $fielddata->nameshortname);
        $field5->set('shortname', $fielddata->nameshortname);
        $field5->set('categoryid', $category0->get('id'));
        $field5->set('type', $fielddata->type);
        $field5->set('configdata', $fielddata->configdata);
        $field5->set_category($category0);
        $field5->save();

        // Check that category have fields and store ids for future checks
        $this->assertCount(6, $category0->fields());

        $category0fieldsids = array();
        foreach ($category0->fields() as $field) {
            $category0fieldsids[] = $field->get('id');
        }

        // Delete category.
        $this->assertTrue($category0->delete());

        // Check that the category fields has been deleted.
        foreach ($category0fieldsids as $fieldid) {
            $this->assertFalse(field::record_exists($fieldid));
        }
    }

    public function test_categories_list() {
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

        // Let's test counts.
        $this->assertCount(6, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category5->delete();
        $this->assertCount(5, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category4->delete();
        $this->assertCount(4, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category3->delete();
        $this->assertCount(3, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category2->delete();
        $this->assertCount(2, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category1->delete();
        $this->assertCount(1, api::list_categories($options['component'], $options['area'], $options['itemid']));
        $category0->delete();
        $this->assertCount(0, api::list_categories($options['component'], $options['area'], $options['itemid']));
    }
}
