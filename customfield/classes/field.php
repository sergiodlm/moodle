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
use Horde\Socket\Client\Exception;
use Phpml\Exception\DatasetException;

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
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return array(
                'shortname' => [
                        'type' => PARAM_TEXT,
                ],
                'name' => [
                        'type' => PARAM_TEXT,
                ],
                'type' => [
                        'type' => PARAM_TEXT,
                ],
                'description' => [
                        'type' => PARAM_RAW,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
                'descriptionformat' => [
                        'type' => PARAM_INT,
                        'default' => FORMAT_MOODLE,
                        'optional' => true
                ],
                'sortorder' => [
                        'type' => PARAM_INT,
                        'default' => 0,
                ],
                'categoryid' => [
                        'type' => PARAM_INT
                ],
                'configdata' => [
                        'type' => PARAM_TEXT,
                        'optional' => true,
                        'default' => null,
                        'null' => NULL_ALLOWED
                ],
        );
    }

    /**
     * Validate the user ID.
     *
     * @param int $value The value.
     * @return true|lang_string
     * @throws \coding_exception
     * @throws \dml_write_exception
     */
    protected function validate_shortname($value) {
        if ( strpos($value, ' ') !== false ) {
            throw new \dml_write_exception(get_string('invalidshortnameerror', 'core_customfield'));
        }

        return true;
    }

    /**
     * @param int|null $value
     * @return int
     * @throws \coding_exception
     */
    public function id(int $value = null): int {
        if (! is_null($value)) {
            $this->set('id', $value);
        }
        return $this->get('id');
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function shortname(): string {
        return $this->get('shortname');
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function name(): string {
        return $this->get('name');
    }

    /**
     * @return string
     * @throws \coding_exception
     */
    public function type(): string {
        return $this->get('type');
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws \coding_exception
     */
    public function description(string $value = null): string {
        if (! is_null($value)) {
            $this->set('description', $value);
        }
        return $this->get('description');
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws \coding_exception
     */
    public function descriptionformat(string $value = null): string {
        if (! is_null($value)) {
            $this->set('descriptionformat', $value);
        }
        return $this->get('descriptionformat');
    }

    /**
     * @return int
     * @throws \coding_exception
     */
    public function sortorder(int $value = null): int {
        if (! is_null($value)) {
            $this->set('sortorder', $value);
        }
        return $this->get('sortorder');
    }

    /**
     * @return int
     * @throws \coding_exception
     */
    public function categoryid(): int {
        return $this->get('categoryid');
    }

    /**
     * @return data|null
     * @throws \coding_exception
     */
    public function data(): data {
        return data::fieldload($this->get('id'));
    }

    /**
     * @param string|null $value
     * @return string|null
     * @throws \coding_exception
     */
    public function configdata(string $value = null): string {
        if (! is_null($value)) {
            $this->set('configdata', $value);
        }
        return $this->get('configdata');
    }

    // Get total count of fields for this category.

    /**
     * @param int $categoryid
     * @return int
     * @throws \dml_exception
     */
    private static function count_fields(int $categoryid) : int {
        global $DB;

        return $DB->count_records(
                self::TABLE,
                [
                        'categoryid' => $categoryid
                ]
        );
    }

    /**
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_count_fields() : int {
        return $this::count_fields( $this->get('categoryid') );
    }

    /**
     * @return bool
     * @throws \coding_exception
     */
    protected function before_delete() : bool {
        if ( $this->data()->id() > 0 ) {
            $this->data()->delete();
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_create() : bool {
        return $this::reorder();
    }

    /**
     * @param bool $result
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected function after_delete($result) :bool  {
        return $this->reorder();
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function static_reorder($categoryid): bool {
        global $DB;

        $fieldneighbours = $DB->get_records(
                self::TABLE,
                [
                        'categoryid' => $categoryid
                ],
                'sortorder DESC');

        $neworder = count($fieldneighbours);

        foreach ($fieldneighbours as $field) {
            $dataobject = new \stdClass();
            $dataobject->id = $field->id;
            $dataobject->sortorder = --$neworder;
            if (!$DB->update_record(self::TABLE, $dataobject)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function reorder() : bool {
        return $this::static_reorder($this->categoryid());
    }

    /**
     * @param int $position
     * @return field
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private function move(int $position): self {
        global $DB;

        $nextfielddata = $DB->get_record(
                $this::TABLE,
                [
                        'sortorder' => $this->sortorder() + $position,
                        'categoryid' => $this->categoryid()
                ]
        );

        if (!empty($nextfielddata)) {
            $previusfield = field_factory::load($nextfielddata->id);
            $previusfield->sortorder($this->sortorder());
            $previusfield->save();
            $this->sortorder($this->sortorder() + $position);
            $this->save();
        }

        return $this;
    }

    /**
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function up(): self {
        return $this->move(1);
    }

    /**
     * @return category
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function down(): self {
        return $this->move(-1);
    }

    /**
     * Tweaks the edit form.
     * @param moodleform $mform instance of the moodleform class
     * @return bool
     */
    public function edit_after_data($mform) {
        if (!$this->is_editable()) {
            return false;
        }
        $this->edit_field_set_locked($mform);
        return true;
    }

    /**
     * TODO: check capabilities.
     *
     * @return field
     */
    public function is_editable() {
        return true;
    }

    /**
     * TODO: check locked status.
     *
     * @return field
     */
    public function is_locked() {
        return false;
    }

    /**
     * HardFreeze the field if locked.
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->get('shortname'))) {
            return;
        }
        if ($this->is_locked() and !has_capability('moodle/course:update', context_course::instance($this->get('courseid')))) {
            $mform->hardFreeze($this->get('shortname'));
            $mform->setConstant($this->get('shortname'), $this->get('data'));
        }
    }

    /**
     * Loads an object with data for this field.
     * @param stdClass $user a user object
     */
    public function edit_load_data($data) {
        if ($this->data() !== null) {
           $data->{$this->get('shortname')} = $this->data()->value();
        }
    }

    public function set_datarecord($datavalues) {
        $data = data::load($datavalues->recordid, $datavalues->fieldid);

        $data->intvalue($datavalues->intvalue);
        $data->decvalue($datavalues->decvalue);
        $data->shortcharvalue($datavalues->shortcharvalue);
        $data->charvalue($datavalues->charvalue);
        $data->value($datavalues->value);
        $data->valueformat($datavalues->valueformat);
        $data->contextid($datavalues->contextid);
        $data->save();
    }

}

