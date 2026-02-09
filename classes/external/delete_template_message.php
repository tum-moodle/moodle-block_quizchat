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
 * PHP library for the block_quizchat plugin.
 *
 * @package   block_quizchat
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_quizchat\external;

use external_function_parameters;
use external_single_structure;
use external_multiple_structure;
use external_value;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/../../lib/lib.php');

class delete_template_message extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'templateid'          => new \external_value(PARAM_INT, 'Template id')
        ]);
    }

    /**
     * Define the structure of the returned data
     * @return external_value
     */
    public static function execute_returns():external_value  {
        return new external_value(PARAM_BOOL, 'The success');
    }
    public static function execute($templateid) {
        return delete_template_msg($templateid);
    }

}
