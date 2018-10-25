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
     * @var category
     */
    protected $category;

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function validate_config_form(array $data, $files = array()): array {
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
                        'type' => PARAM_PLUGIN,
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
                'categoryid'        => [
                        'type' => PARAM_INT
                ],
                'configdata'        => [
                        'type'     => PARAM_RAW,
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
     * @return bool
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
     * Validate if configdata have all required fields
     *
     * @param string $value
     * @return bool
     * @throws \moodle_exception
     */
    protected function validate_configdata($value) {
        $fields = $this->get('configdata');

        if (!(isset($fields['required']) && isset($fields['uniquevalues']))) {
            throw new \moodle_exception('fieldrequired', 'core_customfield');
        }

        return true;
    }

    /**
     * Delete associated data before delete field
     *
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function before_delete() {
        global $DB;
        // TODO execute callback from all plugins so they can delete data associated with this field.
        $DB->execute('DELETE from {' . data::TABLE . '} WHERE fieldid = ?', [$this->get('id')]);
        // TODO delete all files that are associated with field description that is about to be deleted.
    }

    /**
     * Update sort order after create
     */
    protected function after_create() {
        api::move_field($this, $this->get('categoryid'));
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
    public function get_category(): category {
        if (!$this->category) {
            $this->category = new category($this->raw_get('categoryid'));
        }
        return $this->category;
    }

    /**
     * Custom getter for configdata, decoded
     *
     * @return array
     */
    protected function get_configdata(): array {
        return json_decode($this->raw_get('configdata'), true) ?? array();
    }

    /**
     * @param string $property
     * @return mixed
     * @throws \moodle_exception
     */
    public function get_configdata_property(string $property) {
        $configdata = $this->get('configdata');
        if ( !isset($configdata[$property]) ) {
            return null;
        }
        return $configdata[$property];
    }

    /**
     * Returns a correct class field.
     *
     * @param int $id
     * @param \stdClass|null $field
     * @return field
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function load_field(int $id, \stdClass $field = null): field {
        global $DB;

        if (!$field || empty($field->type)) {
            $field = $DB->get_record('customfield_field', ['id' => $id]);
        }

        $customfieldtype = "\\customfield_{$field->type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception(get_string('errorfieldtypenotfound', 'core_customfield', s($field->type)));
        }

        return new $customfieldtype($field->id, $field);
    }

    /**
     * @param string $type
     * @return field
     * @throws \coding_exception
     */
    public static function create_from_type(string $type): field {

        $customfieldtype = "\\customfield_{$type}\\field";
        if (!class_exists($customfieldtype) || !is_subclass_of($customfieldtype, field::class)) {
            throw new \coding_exception(get_string('errorfieldtypenotfound', 'core_customfield', s($type)));
        }

        $field = new $customfieldtype();
        $field->set('type', $type);
        return $field;
    }

    /**
     * @param int $categoryid
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function get_fields_from_category_array(int $categoryid): array {
        global $DB;

        $fields  = array();

        $plugins = \core\plugininfo\customfield::get_enabled_plugins();
        // No plugins enabled.
        if (empty($plugins)) {
            return $plugins;
        }

        list($sqlfields, $params) = $DB->get_in_or_equal(array_keys($plugins), SQL_PARAMS_NAMED);
        $sql = "SELECT *
                  FROM {customfield_field}
                 WHERE categoryid = :categoryid
                   AND type {$sqlfields}
              ORDER BY sortorder";
        $records = $DB->get_records_sql($sql, $params + ['categoryid' => $categoryid]);
        foreach ($records as $fielddata) {
            $fields[] = self::load_field($fielddata->id);
        }
        return $fields;
    }
}
