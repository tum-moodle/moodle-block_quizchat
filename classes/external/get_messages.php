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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/../../lib/lib.php');

class get_messages extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'quizchatid' => new \external_value(PARAM_INT, 'Quizchat id.'),
            'most_recent_msg_id' => new \external_value(PARAM_INT, 'most recent msg id'),
            'langstr_general' => new \external_value(PARAM_RAW, 'language string general'),
            'langstr_group' => new \external_value(PARAM_RAW, 'language string group'),
            'langstr_attempt' => new \external_value(PARAM_RAW, 'language string attempt'),
            'langstr_all' => new \external_value(PARAM_RAW, 'language string all'),
            'langstr_strftimerecentfull' => new \external_value(PARAM_RAW, 'language string strftimerecentfull')
        ]);
    }
    public static function execute_returns() {
        return new \external_single_structure([
            'stats' => new \external_single_structure([
                'msg_total' => new \external_value(PARAM_INT, 'Total number of returned messages'),
                'private' => new \external_value(PARAM_INT, 'Number of private messages'),
                'group' => new \external_value(PARAM_INT, 'Number of group messages')
            ]),
            'messages' => new \external_multiple_structure(
                new external_single_structure([
                    'id' => new \external_value(PARAM_INT, 'Message id.'),
                    'userid' => new \external_value(PARAM_INT, 'User (Sender) id.'),
                    'receiverid' => new \external_value(PARAM_INT, 'User (Receiver) id.'),
                    'groupid' => new \external_value(PARAM_INT, 'Group (Receiver) id.'),
                    'timestamp' => new \external_value(PARAM_INT, 'Timestamp of messages creation.'),
                    'date_part' => new \external_value(PARAM_TEXT, 'Message date'),
                    'message' => new \external_value(PARAM_TEXT, 'Message text body.'),
                    'fullname' => new \external_value(PARAM_TEXT, 'Message sender fullname'),
                    'state' => new \external_value(PARAM_TEXT, 'Sender state in a quiz'),
                    'firstname' => new \external_value(PARAM_TEXT, 'Message sender fullname'),
                    'lastname' => new \external_value(PARAM_TEXT, 'Message sender lastname'),
                    'picture' => new \external_value(PARAM_TEXT, 'Sender small profile picture url'),
                    'rfullname' => new \external_value(PARAM_TEXT, 'Message receiver fullname'),
                    'rfirstname' => new \external_value(PARAM_TEXT, 'Message receiver firstname'),
                    'rlastname' => new \external_value(PARAM_TEXT, 'Message receiver lastname'),
                    'questiontxt'  => new \external_value(PARAM_RAW, 'Question info'),
                    'quizattempt' => new \external_value(PARAM_RAW, 'Quiz Attempt'),
                    'questionid' => new \external_value(PARAM_INT, 'Question id')
                ]), 'One message data set', VALUE_OPTIONAL
            ),
            'p_users' => new \external_multiple_structure(
                new external_single_structure([
                    'userid' => new \external_value(PARAM_INT, 'User id.'),
                    'firstname' => new \external_value(PARAM_TEXT, 'firstname'),
                    'lastname' => new \external_value(PARAM_TEXT, 'lastname'),
                    'fullname' => new \external_value(PARAM_TEXT, 'fullname'),
                    'picture' => new \external_value(PARAM_TEXT, 'picture'),
                    'state' => new \external_value(PARAM_TEXT, 'user state in a quiz'),
                    'message_ids' => new \external_value(PARAM_TEXT, 'Message ids')
                ]), 'One message data set', VALUE_OPTIONAL
            ),
            'groups' => new \external_multiple_structure(
                new external_single_structure([
                    'question_id' => new \external_value(PARAM_INT, 'question id.'),
                    'group_name' => new \external_value(PARAM_TEXT, 'group'),
                    'picture' => new \external_value(PARAM_TEXT, 'picture'),
                    'message_ids' => new \external_value(PARAM_TEXT, 'Message ids')
                ]), 'One message data set', VALUE_OPTIONAL
            )
        ]);
    }
    public static function execute($quizchatid, $most_recent_msg_id, $langstr_general, $langstr_group, $langstr_attempt, $langstr_all, $langstr_strftimerecentfull) {
        return get_msgs($quizchatid, $most_recent_msg_id, $langstr_general, $langstr_group, $langstr_attempt, $langstr_all, $langstr_strftimerecentfull);
    }

}
