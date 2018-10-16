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
     * @throws \moodle_exception
     */
    public function test_create_category() {

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

    }

    // TODO: Exceptions tests
    // TODO: Update name tests
    // TODO: Drag adn drop tests
    // TODO: Visibility tests
    // TODO: Delete tests

}
