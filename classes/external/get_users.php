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

class get_users extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            // For more info check https://moodledev.io/docs/apis/subsystems/external/writing-a-service
            'quizid' => new \external_value(PARAM_INT, 'Quiz id.'),
            'everyonetxt' => new \external_value(PARAM_RAW, 'everyone language string.'),
            'partial_name' => new \external_value(PARAM_RAW, 'Partial firstname or lastname of participant.'),
            'questionid' => new \external_value(PARAM_INT, 'The selected question ID.'),
            'general_txt' => new \external_value(PARAM_RAW, 'general language string'),
            'group_txt' => new \external_value(PARAM_RAW, 'group language string')
        ]);
    }
    public static function execute_returns() {
        return new \external_multiple_structure(
            new external_single_structure([
                'id' => new \external_value(PARAM_INT, 'User id.'),
                'lastname' => new \external_value(PARAM_RAW, 'Users last name.'),
                'firstname' => new \external_value(PARAM_RAW, 'Users first name.'),
                'fullname' => new \external_value(PARAM_RAW, 'Users full name.'),
                'state' => new \external_value(PARAM_RAW, 'Users state in a quiz attempt.'),
                'questionname' => new \external_value(PARAM_RAW, 'Users state in a quiz attempt.'),
                'questionid' => new \external_value(PARAM_INT, 'Question id.')
            ]), 'One user data set', VALUE_OPTIONAL
        );
    }
    public static function execute($quizid, $everyonetxt, $partial_name, $questionid, $general_txt, $group_txt) {
        $users_infos = [];
        // Make sure to get the user whether firstname is typed first or lastname
        // Also account for commas
        $partial_name_sql = preg_replace('/\W/i', ' ', $partial_name);
        $partial_name_sql = preg_replace('/\s+/i', ' ', $partial_name_sql);
        $everyone = $everyonetxt;//get_string('everyone', 'block_quizchat');
        if (!is_null($quizid) && $questionid != -1) {
            $users_infos = get_usersdata($quizid, $partial_name_sql, $questionid, $general_txt);
        }
        // Insert the new element at the specified index
        if(str_contains(strtolower($everyone), strtolower($partial_name_sql))&&$questionid == 0){
            $everyonearray=[
                'id' => QUIZCHAT_ADDRESS_EVERYONE,//everyone 0
                'lastname' => $everyone,
                'firstname' => '',
                'fullname' => $everyone,
                'state' => '',
                'questionname' => $general_txt,
                'questionid' => $questionid
            ];
            array_unshift($users_infos, $everyonearray);
        }
        else if(str_contains(strtolower($everyone), strtolower($partial_name_sql)) && $questionid != 0 && $questionid != -1)
        {
            $qname = get_question_name_by_id($questionid);
            $gname = $group_txt.' '. $qname;
            $grouparray=[
                'id' => QUIZCHAT_ADDRESS_QUESTION_GROUP,//Group messaging -2
                'lastname' => $gname,
                'firstname' => '',
                'fullname' => $gname,
                'state' => '',
                'questionname' => $qname,
                'questionid' => $questionid
            ];
            array_unshift($users_infos, $grouparray);
        }

        return $users_infos;
    }
}
