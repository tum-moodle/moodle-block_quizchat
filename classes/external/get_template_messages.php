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

class get_template_messages extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'templateid'          => new \external_value(PARAM_INT, 'Template id'),
            'onlyenabled'          => new \external_value(PARAM_BOOL, 'true if only enabled templates to be retrieved'),
            'quizchatid' => new \external_value(PARAM_INT, 'quizchatid in case of get template in block and null otherwise'),
            'partial_name' => new \external_value(PARAM_RAW, 'Partial name of template message title'),
            'centraltempflag' => new \external_value(PARAM_BOOL, 'true if central template messages are in use')
        ]);
    }

    /**
     * Define the structure of the returned data
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                '0' => new external_value(PARAM_RAW, 'Action links (HTML)'),
                '1' => new external_value(PARAM_RAW, 'Template title'),
                '2' => new external_value(PARAM_RAW, 'Template message'),
                '3' => new external_value(PARAM_RAW, 'Enabled/Disabled as text'),
                '4' => new external_value(PARAM_RAW, 'Created by (fullname)'),
                '5' => new external_value(PARAM_RAW, 'Modified date'),
                '6' => new external_value(PARAM_INT, 'Template id'),
                '7' => new external_value(PARAM_BOOL, 'isenabled flag')
            ])
        );
    }
    public static function execute($templateid, $onlyenabled, $quizchatid, $partial_name, $centraltempflag) {
        $partial_name_sql = preg_replace('/\W/i', ' ', $partial_name);//replaces all non-word characters (including underscores) with a space.
        $partial_name_sql = preg_replace('/\s+/i', ' ', $partial_name_sql);//replaces all occurrences of one or more whitespace characters with a single space.
        return get_template_messages($partial_name_sql,$templateid, $onlyenabled, $quizchatid, $centraltempflag);
    }

}
