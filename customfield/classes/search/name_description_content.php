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
 * Search area for customfield (name, description and content).
 *
 * @package core_customfield
 * @copyright Toni Barbera <toni@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield\search;

defined('MOODLE_INTERNAL') || die();

/**
 * Search area for customfield (name, description and content).
 *
 *
 * @package core_customfield
 * @copyright Toni Barbera <toni@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class name_description_content extends \core_search\base {

    /**
     * Gets a link to the section.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_context_url(\core_search\document $doc) : \moodle_url {
    // TODO: Implement get_context_url() method.
    }

    /**
     * Returns the document associated with this section.
     *
     * @param \stdClass $record
     * @param array $options
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) : \core_search\document {
        // TODO: Implement get_document() method.
    }

    /**
     * Gets a link to the section.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     */
    public function get_doc_url(\core_search\document $doc) : \moodle_url {
        // TODO: Implement get_doc_url() method.
    }

    /**
     * Whether the user can access the section or not.
     *
     * @param int $id The course section id.
     * @return int One of the \core_search\manager:ACCESS_xx constants
     */
    public function check_access($id) : int {
        // TODO: Implement check_access() method.
    }

}