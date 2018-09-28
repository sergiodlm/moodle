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
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_customfield;

use core\persistent;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package core_customfield
 */
abstract class field extends persistent {
    /**
     * Database table.
     */
    const TABLE = 'customfield_field';

    /**
     * Data for field.
     *
     * @var string
     */
    protected $data;

    /**
     * @var category
     */
    protected $category;

    /**
     * Add field parameters to the field configuration form
     *
     * @param \MoodleQuickForm $mform
     */
    abstract public function add_field_to_config_form(\MoodleQuickForm $mform);

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function validate_config_form(array $data, $files = array()) : array {
        return array();
    }

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'shortname'         => [
                        'type' => PARAM_TEXT,
                ],
                'name'              => [
                        'type' => PARAM_TEXT,
                ],
                'type'              => [
                        'type' => PARAM_TEXT,
                ],
                'description'       => [
                        'type'     => PARAM_RAW,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
                'descriptionformat' => [
                        'type'     => PARAM_INT,
                        'default'  => FORMAT_MOODLE,
                        'optional' => true
                ],
                'sortorder'         => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'required'          => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'locked'            => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'uniquevalues'      => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'visibility'        => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'categoryid'        => [
                        'type' => PARAM_INT
                ],
                'configdata'        => [
                        'type'     => PARAM_TEXT,
                        'optional' => true,
                        'default'  => null,
                        'null'     => NULL_ALLOWED
                ],
        );
    }

    /**
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|\lang_string
     * @throws \moodle_exception
     * @throws \dml_write_exception
     */
    protected function validate_shortname($value) {
        if (strpos($value, ' ') !== false) {
            throw new \dml_write_exception(get_string('invalidshortnameerror', 'core_customfield'));
        }

        return true;
    }

    /**
     * @return data|null
     * @throws \moodle_exception
     */
    public function data(): data {
        return data::fieldload($this->get('id'));
    }

    /**
     * Get total count of fields for this category
     *
     * @param int $categoryid
     * @return int
     * @throws \dml_exception
     */
    private static function count_fields(int $categoryid) {
        global $DB;

        return $DB->count_records(
                self::TABLE,
                [
                        'categoryid' => $categoryid
                ]
        );
    }

    /**
     * Call count_fields()
     *
     * @return int
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function get_count_fields(): int {
        return $this::count_fields($this->get('categoryid'));
    }

    /**
     * Delete associated data before delete field
     *
     * @return bool
     * @throws \moodle_exception
     */
    protected function before_delete(): bool {
        if ($this->data()->get('id') > 0) {
            $this->data()->delete();
            return false;
        }
        return true;
    }

    /**
     * Update sort order after create
     *
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function after_create(): bool {
        return $this::reorder();
    }

    /**
     * Update sort order after delete
     *
     * @param bool $result
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    protected function after_delete($result): bool {
        return $this->reorder();
    }

    /**
     * Call category::reorder_fields
     *
     * @return bool
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    private function reorder(): bool {
        return category::reorder_fields($this->get('categoryid'));
    }

    /**
     * Update sort order (used on drag and drop)
     *
     * @param int $position
     * @return field
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    private function move(int $position): self {
        global $DB;

        $nextfielddata = $DB->get_record(
                $this::TABLE,
                [
                        'sortorder'  => $this->get('sortorder') + $position,
                        'categoryid' => $this->get('categoryid')
                ]
        );

        if (!empty($nextfielddata)) {
            $previusfield = field_factory::load($nextfielddata->id);
            $previusfield->set('sortorder', $this->get('sortorder'));
            $previusfield->save();
            $this->set('sortorder', $this->get('sortorder') + $position);
            $this->save();
        }

        return $this;
    }

    /**
     * Update sort order (used on drag and drop)
     *
     * @return self
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function up(): self {
        return $this->move(1);
    }

    /**
     * Update sort order (used on drag and drop)
     *
     * @return self
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public function down(): self {
        return $this->move(-1);
    }

    /**
     * Set the category associated with this field
     *
     * @param category $category
     */
    public function set_category(category $category) {
        $this->category = $category;
    }

    /**
     * Get the category associated with this field
     *
     * @return category
     * @throws \moodle_exception
     */
    public function get_category() : category {
        if (!$this->category) {
            $this->category = new category($this->get('categoryid'));
        }
        return $this->category;
    }
}
