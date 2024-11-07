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
class create_message extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'quizchatid' => new \external_value(PARAM_INT, 'Quizchat id.'),
            'receiverid' => new \external_value(PARAM_INT, 'User (Receiver) id.'),
            'groupid' => new \external_value(PARAM_INT, 'Group  id.'),
            // For some reason '<' is not accepted as text
            // -> use PARAM_RAW instead of PARAM_TEXT and replace '<'
            'message' => new \external_value(PARAM_RAW, 'Message text body.'),
            'questionattemptid' => new \external_value(PARAM_INT, 'Question attempt  id.'),
            'questionid' => new \external_value(PARAM_INT, 'Question id.'),
        ], 'One message data set sent by the client.', VALUE_OPTIONAL);
    }
    public static function execute_returns() {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Message id (negative in case of error).'),
        ]);
    }
    public static function execute($quizchatid, $receiverid, $groupid, $message, $questionattemptid, $questionid) {
        // For some reason '<' is not accepted as text
        $message = str_replace('<', '&lt;', $message);
        $message = str_replace('>', '&gt;', $message);
        $new_msg_id = static::push_message(
            $quizchatid,
            $receiverid,
            $message,
            $groupid,
            $questionattemptid,
            $questionid
        );
        return ['id' => $new_msg_id];
    }

    public static function push_message($quizchatid, $receiverid, $messagetext, $groupid, $questionattemptid, $questionid){
        return create_msg($quizchatid, $receiverid, $messagetext, $groupid, $questionattemptid, $questionid);
    }
}
