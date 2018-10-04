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
                'categoryid'        => [
                        'type' => PARAM_INT
                ],
                'required'          => [
                        'type'    => PARAM_INT,
                        'default' => 0,
                ],
                'uniquevalues'      => [
                        'type'    => PARAM_INT,
                        'default' => 0,
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
        $fields = json_decode($this->get('configdata'), true);

        if (!(isset($fields['required']) && isset($fields['uniquevalues']))) {
            throw new \moodle_exception('fieldrequired', 'core_customfield');
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
        // TODO why is it static, why is it needed?
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
        // TODO this is not used
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
        // TODO why not: $this->get_category()->reorder_fields();
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
            $previusfield = field::load_field($nextfielddata->id);
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
    public function get_category(): category {
        if (!$this->category) {
            $this->category = new category($this->get('categoryid'));
        }
        return $this->category;
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

        // Set default configdata from database
        // TODO use get_field_configdata
        $configdata          = json_decode($field->configdata);
        $field->required     = $configdata->required;
        $field->uniquevalues = $configdata->uniquevalues;

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
        $records = $DB->get_records('customfield_field', ['categoryid' => $categoryid], 'sortorder DESC');
        foreach ($records as $fielddata) {
            $fields[] = self::load_field($fielddata->id);
        }
        return $fields;
    }

    /**
     * @param int $from
     * @param int $to
     * @param int $category
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function drag_and_drop(int $from, int $to, int $category): bool {
        $fieldfrom = self::load_field($from);

        if ($fieldfrom->get('categoryid') != $category) {
            $oldcategory = $fieldfrom->get('categoryid');

            $fieldfrom->set('categoryid', $category);
            $fieldfrom->set('sortorder', -1);
            $fieldfrom->save();

            category::reorder_fields($oldcategory);
            category::reorder_fields($fieldfrom->get('categoryid'));
        }

        if ($to > 0) {
            $fieldto = self::load_field($to);
            if ($fieldfrom->get('sortorder') < $fieldto->get('sortorder')) {
                for ($i = $fieldfrom->get('sortorder'); $i < $fieldto->get('sortorder'); $i++) {
                    $fieldfrom->up();
                }
            } else if ($fieldfrom->get('sortorder') > $fieldto->get('sortorder')) {
                for ($i = $fieldfrom->get('sortorder'); $i > $fieldto->get('sortorder') + 1; $i--) {
                    $fieldfrom->down();
                }
            }
        } else {
            for ($i = $fieldfrom->get('sortorder'); $i > 0; $i--) {
                $fieldfrom->down();
            }
        }

        return true;
    }
}
