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
 * Represents a user key from the user_private_key table.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

class user_key {

    /** @var int */
    private $id;

    /** @var string */
    private $script;

    /** @var string */
    private $value;

    /** @var int */
    private $userid;

    /** @var int */
    private $instance;

    /** @var string */
    private $iprestriction;

    /** @var int */
    private $validuntil;

    /** @var int */
    private $timecreated;

    /**
     * Default user_key constructor.
     *
     * @param \stdClass $key Data from user_private_key table.
     */
    public function __construct(\stdClass $key) {
        $this->id = $key->id;
        $this->script = $key->script;
        $this->value = $key->value;
        $this->userid = $key->userid;
        $this->instance = $key->instance;
        $this->iprestriction = $key->iprestriction;
        $this->validuntil = $key->validuntil;
        $this->timecreated = $key->timecreated;
    }

    /**
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return string
     */
    public function get_script(): string {
        return $this->script;
    }

    /**
     * @return string
     */
    public function get_value(): string {
        return $this->value;
    }

    /**
     * @return int
     */
    public function get_userid(): int {
        return $this->userid;
    }

    /**
     * @return int|null
     */
    public function get_instance(): ?int {
        return $this->instance;
    }

    /**
     * @return string|null
     */
    public function get_iprestriction(): ?string {
        return $this->iprestriction;
    }

    /**
     * @return int|null
     */
    public function get_validuntil(): ?int {
        return $this->validuntil;
    }

    /**
     * @return int
     */
    public function get_timecreated(): int {
        return $this->timecreated;
    }
}
