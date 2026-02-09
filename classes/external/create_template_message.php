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
use core_block\fetch_addable_blocks;
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/../../lib/lib.php');
class create_template_message extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'title' => new \external_value(PARAM_RAW, 'template message title'),
            'template' => new \external_value(PARAM_RAW, 'template message body'),
            'isenabled' => new \external_value(PARAM_BOOL, 'whether this template is enabled for site-level use'),
            'isquizlevel' => new \external_value(PARAM_BOOL, 'whether this template is for site-level or block-level use'),
            'templateid' => new \external_value(PARAM_INT, 'template id in case of edit template and null if create template'),
            'quizchatid' => new \external_value(PARAM_INT, 'quizchatid in case of add template in block and null otherwise'),
        ], 'One message data set sent by the client.', VALUE_OPTIONAL);
    }
    public static function execute_returns() {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Template id (negative in case of error).'),
        ]);
    }
    public static function execute($title, $template, $isenabled, $isquizlevel, $templateid, $quizchatid) {
        // For some reason '<' is not accepted as text
        $template = str_replace('<', '&lt;', $template);
        $template = str_replace('>', '&gt;', $template);
        $new_temp_id = static::push_temp(
            $title, $template, $isenabled, $isquizlevel, $templateid, $quizchatid
        );
        return ['id' => $new_temp_id];
    }

    public static function push_temp($title, $template, $isenabled, $isquizlevel, $templateid, $quizchatid){
        return create_temp_msg($title, $template, $isenabled, $isquizlevel, $templateid, $quizchatid);
    }
}
