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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
require_once(__DIR__ . '/../../lib/lib.php');
class exclude_central_template_messages extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() { 
        return new external_function_parameters([
        'templates' => new external_multiple_structure(
            new external_single_structure([
                'templateid'      => new external_value(PARAM_INT,  'Template ID'),
                'enabled' => new external_value(PARAM_BOOL, '1 = enabled, 0 = disabled'),
                'quizchatid'      => new external_value(PARAM_INT,  'Quizchat ID'),
            ])
        )
    ]); 
    // templates: 
    //     [
    //         { "templateid": 94, "enabled": 1, "quizchatid": 1 },
    //         { "templateid": 91, "enabled": 0, "quizchatid": 1 },
    //         { "templateid": 90, "enabled": 1, "quizchatid": 1 }
    //     ]
    }
    public static function execute_returns() {
        return new \external_single_structure([
            'updated' => new \external_value(PARAM_BOOL, 'Updated flag'),
        ]);
    }
    public static function execute($templates) {
        $updated = static::push_temp($templates);
        return ['updated' => $updated];
    }

    public static function push_temp($templates){
         // Validate input parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'templates' => $templates
        ]);
        $updated = true;

        foreach ($params['templates'] as $t) {//$t['id']
            $t_id = create_temp_msg("", "", $t['enabled'], false, $t['templateid'], $t['quizchatid']); 
            // If any result <= 0, mark the entire operation as failed
            if ($t_id <= 0) {
                $updated = false;
            }
        }
        return $updated; 
    }
}
