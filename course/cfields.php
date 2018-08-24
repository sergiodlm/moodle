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
 * @package core
 * @copyright 2018 Toni Barbera (toni@moodle.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $OUTPUT, $PAGE, $CFG;

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

global $PAGE;

admin_externalpage_setup('course_cfields');

$output = $PAGE->get_renderer('core_cfield');
$handler = new  \core_course\cfield\course_handler();
$outputpage = new \core_cfield\output\management($handler);

echo $output->header();
echo $output->render($outputpage);
echo $output->footer();




