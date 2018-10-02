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
 * Search area for course custom fields.
 *
 * @package core_customfield
 * @copyright Toni Barbera <toni@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield\search;

defined('MOODLE_INTERNAL') || die();

/**
 * Search area for course sections (title and summary).
 *
 * Note this does not include the activities within the section, as these have their own search
 * areas.
 *
 * @package core_customfield
 * @copyright Toni Barbera <toni@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_customfields extends \core_search\base_mod {

    /**
     * The context levels the search area is working on.
     *
     * @var array
     */
    protected static $levels = [CONTEXT_USER];

    /**
     * Returns a url to the course.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public function get_context_url(\core_search\document $doc) {
        return $this->get_doc_url($doc);
    }


    /**
     * Returns a url to the course.
     *
     * @param \core_search\document $doc
     * @return \moodle_url
     * @throws \moodle_exception
     */
    public function get_doc_url(\core_search\document $doc) {
        return new \moodle_url('/course/view.php', array('id' => $doc->get('courseid')));
    }

    /**
     * Returns the document associated with this section.
     *
     * @param \stdClass $record
     * @param array $options
     * @return \core_search\document|bool
     * @throws \moodle_exception
     */
    public function get_document($record, $options = array()) {

        try {
            $context = \context_module::instance($record->contextid);
        } catch (\dml_missing_record_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document, not all required data is available: ' .
                      $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        } catch (\dml_exception $ex) {
            // Notify it as we run here as admin, we should see everything.
            debugging('Error retrieving ' . $this->areaid . ' ' . $record->id . ' document: ' . $ex->getMessage(), DEBUG_DEVELOPER);
            return false;
        }

        // Get the non-null $record(Data) value. Only 1 must be !null
        $datavalue = (string) ($record->intvalue ??
                               $record->decvalue ??
                               $record->shortcharvalue ??
                               $record->charvalue ??
                               $record->value);

        // Prepare associative array with data from DB.
        $doc = \core_search\document_factory::instance($record->id, $this->componentname, $this->areaname);
        $doc->set('title', content_to_text($datavalue, false));
        $doc->set('content', content_to_text($datavalue, \core_search\manager::TYPE_TEXT));
        $doc->set('contextid', $context->id);
        $doc->set('courseid', $record->recordid);
        $doc->set('owneruserid', \core_search\manager::NO_OWNER_ID);
        $doc->set('modified', $record->timemodified);

        // Check if this document should be considered new.
        if (isset($options['lastindexedtime']) && ($options['lastindexedtime'] < $record->timecreated)) {
            // If the document was created after the last index time, it must be new.
            $doc->set_is_new(true);
        }

        return $doc;
    }



    public function check_access($id) {
        // TODO: Implement check_access() method.
        return \core_search\manager::ACCESS_GRANTED;
    }

    /**
     * Returns recordset containing required data for indexing course customfields
     *
     * @param int $modifiedfrom timestamp
     * @param \context|null $context Restriction context
     * @return \moodle_recordset|null Recordset or null if no change possible
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function get_document_recordset($modifiedfrom = 0, \context $context = null) {
        global $DB;

        list ($contextjoin, $contextparams) = $this->get_context_restriction_sql(
                $context, 'data', 'd');
        if ($contextjoin === null) {
            return null;
        }

        return $DB->get_recordset_sql(
                "SELECT d.* 
                FROM {customfield_data} d
                $contextjoin
                WHERE d.timemodified >= ? 
                ORDER BY d.timemodified ASC",
                array_merge($contextparams, [$modifiedfrom])
        );
    }

}
