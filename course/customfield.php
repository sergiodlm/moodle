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

require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('course_customfield');

$PAGE->requires->string_for_js('errorreloadpage', 'core_customfield');

$output = $PAGE->get_renderer('core_customfield');
$handler = new  \core_course\customfield\course_handler();
$outputpage = new \core_customfield\output\management($handler);

echo $output->header(),
     $output->render($outputpage),
     $output->footer();
