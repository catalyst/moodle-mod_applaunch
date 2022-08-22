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
 * PHPUnit generator for applaunch activity.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @author     Kevin Pham <kevinpham@catalyst-au.net>
 * @copyright  Catalyst IT, 2022
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch\testing;

use mod_applaunch\app_type;

final class generator extends \core\testing\mod_generator {

    /**
     * Create a instance of mod_applaunch for phpunit tests.
     *
     * @param null $record
     * @param array|null $options
     * @return stdClass
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;
        $uniqueid = random_string(6);

        // Generate an app type.
        $apptype = new app_type(0, (object) [
            'name' => 'App type ' . $uniqueid,
            'description' => '',
            'url' => "testapp://test$uniqueid.com",
            'enabled' => 1,
        ]);
        $apptype->save();

        // Generate the applaunch instance.
        $defaultsettings = array(
            'name' => 'App launcher ' . $uniqueid,
            'description' => '',
            'urlslug' => '',
            'apptypeid' => $apptype->get('id'),
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
