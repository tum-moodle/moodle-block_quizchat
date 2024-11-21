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
 * Library of functions and constants for the block_quizchat plugin.
 *
 * @package   block_quizchat
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// In lack of actual user groups use 0 for everyone and 1 for instructors
define('QUIZCHAT_ADDRESS_EVERYONE', 0);
define('QUIZCHAT_ADDRESS_INSTRUCTORS', -1);
define('QUIZCHAT_ADDRESS_QUESTION_GROUP', -2);
define('QUIZCHAT_GENERAL_QUESTION_ID', 0);
define('QUIZCHAT_STUDENT_QUESTION_ID', -1);
define('QUIZCHAT_POLL_TIMEOUT_MIN', 5);
define('QUIZCHAT_UNNOTIFY_TIMEOUT_MIN', 3);
define('QUIZCHAT_POLL_TIMEOUT_MAX', 60);
define('QUIZCHAT_UNNOTIFY_TIMEOUT_MAX', 60);
define('QUIZCHAT_MSG_LENGTH_MIN', 1);
define('QUIZCHAT_MSG_LENGTH_MAX', 5000);

/**
 * Given an object containing all the necessary data,
 * (defined by the form in block_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $quizchat
 * @return int
 */
function quizchat_add_instance($quizchat) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');

    $quizchat->timecreated = time();
    $returnid = $DB->insert_record("block_quizchat", $quizchat);
    return $returnid;
}

/**
 * Checks if the current user has sendall-capability in a quizchat block context.
 * @param stdClass $quizchat quizchat record
 * @param int $userid userid
 * @return bool true if the current user has sendall-capability
 */

function check_sendallcap($quizchat, $userid = null)
{
    global $DB;
    //sendall capability name as defined in access.php
    $sendallcapname = 'block/quizchat:sendall';
    $blkinstid = $quizchat->instanceid;
    // getting the context of that block instance
    // according to https://moodledev.io/docs/apis/subsystems/access
    // block context fetching: $contextblock = context_block::instance($this->instance->id);(the block instance)
    $quizchatblockcontext = \context_block::instance($blkinstid);

    // checking capbility of sendallcapname in quizchatblockcontext
    // according to https://moodledev.io/docs/apis/subsystems/access
    // has_capability Checks whether a user has a particular capability in a given context. By default checks the capabilities of the current user.
    $checkcurrentusercap = has_capability($sendallcapname, $quizchatblockcontext, $userid);

    return $checkcurrentusercap;
}

/**
 * Checks if the current user has sendmsg-capability in a quizchat block context.
 * @param stdClass $quizchat quizchat record
 * @return bool true if the current user has sendmsg-capability in a quizchat block context
 */

 function check_sendmsgcap($quizchat)
 {
     global $DB;
     //sendall capability name as defined in access.php
     $sendmsgcapname = 'block/quizchat:sendmsg';
     $blkinstid = $quizchat->instanceid;
     // getting the context of that block instance
     $quizchatblockcontext = \context_block::instance($blkinstid);
 
     // checking capbility of sendmsgcapname in quizchatblockcontext
     // has_capability Checks whether a user has a particular capability in a given context. By default checks the capabilities of the current user.
     $checkcurrentusercap = has_capability($sendmsgcapname, $quizchatblockcontext);
 
     return $checkcurrentusercap;
 }

/**
 * Checks if student role has sendmsg-capability in a quizchat block context.
 * @param stdClass $quizchat quizchat record
 * @return bool true if student role has sendmsg-capability in a quizchat block context
 */

 function check_sendmsgcap_students($quizchat)
 {
     global $DB,$CFG;
     require_once($CFG->dirroot . '/' . $CFG->admin . '/tool/capability/locallib.php');
     //sendall capability name as defined in access.php
     $sendmsgcapname = 'block/quizchat:sendmsg';
     $blkinstid = $quizchat->instanceid;
     $system_context_id= \context_system::instance()->id;
     $roles = array();
     $student = $DB->get_record('role', array('shortname' => 'student'), '*', MUST_EXIST);
     $roles[$student->id] = $student;
     $capabilitycontexts[$sendmsgcapname] = tool_capability_calculate_role_data($sendmsgcapname, $roles);
     $contexts = $capabilitycontexts[$sendmsgcapname];
     // Now find out what access is given to student role
     $permission = 0;
     if(isset($contexts[$quizchat->contextid])) {
        $permission = $contexts[$quizchat->contextid]->rolecapabilities[$student->id];
     }
     else {
        $permission = $contexts[$system_context_id]->rolecapabilities[$student->id];
     }
    $allowed = (($permission == 0 || $permission == '-1') ? false : true);
    return $allowed;
 }

/**
 * Return the small user profile image url for the given user id.
 * @param int $userid user id
 * @return string The small profile image url
 */

 function get_user_pic_url($userid)
 {
    global $PAGE;
    $user = \core_user::get_user($userid);
    $userimg = new \user_picture($user);
    $userimg->size = 0;
    if(!isset($PAGE->context))
    {
        $PAGE->set_context(context_system::instance());
    }
    $imgurl = $userimg->get_url($PAGE)->out(false);

     return $imgurl;
 }

/**
 * Returns array  of users data including 'id','firstname','lastname','fullname','profileimageurlsmall','state' from users table for specific userids in a quiz.
 * If the fetched user is enrolled in the given quizid, the 'state' field is the user most recent attempt state in that quiz from quiz_attempts table.
 * It returns 'abandoned','inprogress','noattempt','finished'.
 * If the fetched user is not anymore enrolled in the given quizid(previously was enrolled and has Msgs in the given quizid),
 * the 'state' field states whether the fetched user is 'suspended', 'deleted', or just 'unenrolled'.
 * The fuction will return '' as value for 'profileimageurlsmall', if the user profile picture is not needed.
 * @param array $users array of users ids to be fetched, or empty array if it is required to return all enrolled users data in the given quizid.
 * @param int $quizid quiz id.
 * @param bool $queryByName bool to check whether user query is by id (msg header) or (partial) name (menu)
 * @param string $searchText text to search for in the enrolled users and match it with user firstname or last name in user table.
 * @param int $questionid the selected question id.
 * @param string $general_txt language string general
 * @return array array of user details:'id','firstname','lastname','fullname','profileimageurlsmall','state'.
 */
 function get_usersdata($users, $quizid, $queryByName, $searchText, $questionid, $general_txt)
 {
    $users_infos = [];
    if(!is_null($quizid)) {
        global $DB;

        ////Get enrolled users in quiz/course
        // Get course id from quizid and then get coursecontext to get enrolled users in that context
        $quiz = $DB->get_record('quiz', array('id' => $quizid));
        //get enrolled users in a course
        $enrolled = get_enrolled_users_in_course($quiz->course);
        $enrolledids = array_column($enrolled, 'id');

        //participants in case of Msg-header are chat-participants(userIds)
        //participants in case of Participants-Menu are enrolled users(enrolledids)
        if ($queryByName) // Participants-Menu
        {
            // Compare lower case search names for lack of
            // 'utf8mb4_unicode_ci' collation in postgresql
            $searchText = strtolower($searchText);
            $fetchedids = $enrolledids;
        }
        else //Msg-header case
        {
            // Remove null or duplicate values from the array
            $userIds = array_filter($users, function ($id) {
                return !is_null($id) && $id !== '';
            });
            //$userIds = array_values(array_column($userIds, null, 'id'));
            $userIds = array_unique($userIds);
            $fetchedids = $userIds;
        }

        if (0 < $fetchedids && $enrolledids) {
            //get user infos by id
            $participants = $DB->get_records_sql("
                SELECT id, firstname, lastname, fullname, picture, deleted, suspended FROM
                (
                    SELECT  id, firstname, lastname, picture, deleted, suspended,
                    CONCAT(lastname, ' ', firstname) AS fullname, CONCAT(firstname, ' ', lastname) AS fullnameRvs
                    FROM {user}
                    WHERE id IN (" . implode(',', $fetchedids) . ")
                ) as preselection
                WHERE LOWER(fullname) LIKE '%{$searchText}%' OR  LOWER(fullnameRvs) LIKE '%{$searchText}%'
                ORDER BY fullname ASC;
            ");

            foreach ($participants as $participant) {
                $filtereduser = [];
                //if get image isn't needed, this function called for participants-menu,
                //the input user array doesn't contain unenrolled or deleted users. All users are enrolled.
                // Get the state of the most recent attempt from the quiz_attempts table
                $attemptstate = get_user_state_inquiz($participant->id, $quizid);
                //then the input user array may contain unenrolled or deleted users
                //user pic
                $imgurl = get_user_pic_url($participant->id);
                //if chat-participant is enrolled in quiz/course
                if (in_array($participant->id, $enrolledids)) {
                    // Get the state of the most recent attempt from the quiz_attempts table
                    $attemptstate = get_user_state_inquiz($participant->id, $quizid);
                } else {
                    //unenrolled chat-participant
                    $attemptstate = 'unenrolled';
                }
                if ($participant->suspended == '1') {
                    $attemptstate = 'suspended';
                }
                // deleted / suspended user (suspended user can still be enrolled)
                if($participant->deleted == '1') {
                    $attemptstate = 'deleted';
                }
                if (!empty($participant)) {
                    $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quizid));
                    if($questionid != QUIZCHAT_GENERAL_QUESTION_ID) {//filter participants menu with question id
                        if(!check_sendallcap($quizchat, $participant->id )) {
                            $participant_question_query = "SELECT qa.*, ques_a.questionid, q.name as questionname, ques_a.slot
                                FROM {quiz_attempts} qa
                                JOIN {question_attempts} ques_a
                                ON qa.uniqueid = ques_a.questionusageid
                                JOIN {question} q
                                ON q.id = ques_a.questionid
                                WHERE qa.quiz = ".$quizid."
                                AND qa.timestart = (
                                    SELECT MAX(qa_max.timestart)
                                    FROM {quiz_attempts} qa_max
                                    WHERE qa_max.quiz = qa.quiz
                                    AND qa_max.userid = qa.userid
                                )
                                AND ques_a.questionid = ".$questionid.";"; 
                            $participants_question = $DB->get_records_sql($participant_question_query);
                            // Extract the userid values into a separate array
                            $userIds = array_column($participants_question, 'userid');

                            // Check if $participant->id exists in $userIds
                            if (in_array($participant->id, $userIds)) {
                                $filtereduser = [
                                    'id' => $participant->id,
                                    'firstname' => $participant->firstname,
                                    'lastname' => $participant->lastname,
                                    'fullname' => $participant -> lastname . ', ' . $participant -> firstname,
                                    'profileimageurlsmall'=> $imgurl,
                                    'state' => $attemptstate,
                                    'questionname' => get_question_name_by_id($questionid),
                                    'questionid' => $questionid
                                ];
                                array_push($users_infos, $filtereduser);
                            }
                        }
                        else {
                                $filtereduser = [
                                    'id' => $participant->id,
                                    'firstname' => $participant->firstname,
                                    'lastname' => $participant->lastname,
                                    'fullname' => $participant -> lastname . ', ' . $participant -> firstname,
                                    'profileimageurlsmall'=> $imgurl,
                                    'state' => $attemptstate,
                                    'questionname' => get_question_name_by_id($questionid),
                                    'questionid' => $questionid
                                ];
                                array_push($users_infos, $filtereduser);

                        }
                    }
                    else {
                        $filtereduser = [
                            'id' => $participant->id,
                            'firstname' => $participant->firstname,
                            'lastname' => $participant->lastname,
                            'fullname' => $participant -> lastname . ', ' . $participant -> firstname,
                            'profileimageurlsmall'=> $imgurl,
                            'state' => $attemptstate,
                            'questionname' => $general_txt,//get_string('student_question_general', 'block_quizchat'),
                            'questionid' => $questionid
                        ];
                        array_push($users_infos, $filtereduser);
                    }
                }
            }
        }
    }
    return $users_infos;
 }

 /**
 * Get the user state of the most recent attempt in a quiz from quiz_attempts table with quizid and userid
 * @param int $userid user id
 * @param int $quizid quiz id
 * @return string the state value stored in quiz_attempts table. It returns one of 4 values: finished - abandoned - inprogress - noattempt
 */
 function get_user_state_inquiz($userid,$quizid)
 {
    global $DB;
    // Get the state of the most recent attempt from the quiz_attempts table
    $attemptstate = $DB->get_field_sql(
        "SELECT state
        FROM {quiz_attempts}
        WHERE quiz = :quizid AND userid = :userid
        ORDER BY timefinish ASC
        LIMIT 1",
        ['quizid' => $quizid, 'userid' => $userid]
    );
    //No attempt found.
    if($attemptstate === false) {
        $attemptstate = 'noattempt';
    }
    return $attemptstate;
 }

  /**
 * Get the enrolled users in a quiz without suspended users
 * @param int $courseid course id
 * @return array enrolled ids
 */
function get_enrolled_users_in_course($courseid)
{
   global $DB;
   $sqlquery =
   "SELECT
   u.id AS id
FROM
   {user_enrolments} ue
JOIN
   {enrol} e ON ue.enrolid = e.id
JOIN
   {user} u ON ue.userid = u.id
WHERE
   e.courseid = ".$courseid." and u.suspended = 0;";
   $enrolledids = $DB->get_records_sql($sqlquery);
   return $enrolledids;
}

 /**
 * Get quiz attempt id by quesion attempt id
 * @param int $questionattemptid quesion attempt id
 * @return int quiz attempt id
 */
function get_quizattid_by_quesattid ($questionattemptid) {
    global $DB;
    $query = "SELECT slot, questionusageid, questionid
    FROM {question_attempts}
    WHERE id= ".$questionattemptid.";";
    $question_attempt_record = $DB->get_record_sql($query);
    //$slot = $question_attempt_record->slot;
    $questionusageid = $question_attempt_record->questionusageid;
    //$questionid = $question_attempt_record->questionid;
    $quizattempt_query = "SELECT id,attempt
    FROM {quiz_attempts}
    WHERE uniqueid= ".$questionusageid.";";
    $quizattempt_record = $DB->get_record_sql($quizattempt_query);
    $quizattemptid = $quizattempt_record->id;
    return $quizattemptid;
}

 /**
 * Get the messages of a quizchat
 * @param int $quizchatid quizchat id
 * @param int $most_recent_msg_id most recent msg id
 * @param string $langstr_general language string general
 * @param string $langstr_group language string group
 * @param string $langstr_attempt language string attempt
 * @return array messages array
 */
 function get_msgs($quizchatid,$most_recent_msg_id, $langstr_general, $langstr_group, $langstr_attempt, $langstr_all, $langstr_strftimerecentfull) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot.'/mod/quiz/lib.php');
    $grp_langstr = $langstr_group;
    $msgStruct = [
        "stats" => [
            "msg_total" => 0,
            "private" => 0,
            "group" => 0
        ],
        "messages" => [],
        "p_users" => [],
        "groups" => []
    ];
    // $PAGE->cm is not available so let's get the quizid from the quizchat tbl
    $quizchat = $DB->get_record('block_quizchat', array('id' => $quizchatid));
    //check sendall capability of the current user
    $hascap=check_sendallcap($quizchat);
    // Check if the user has any attempts for the quiz
    $attempts = quiz_get_user_attempts($quizchat->quiz, $USER->id,'all',true);
    $enableblock = check_blockavailability($quizchat->quiz);
if ($enableblock && (($attempts && !$hascap)|| $hascap)) {
    $allgrp_id = intVal($DB->get_record('block_quizchat_group', array('name' => 'all'))->id);
    $teachersgrp_id = intVal($DB->get_record('block_quizchat_group', array('name' => 'teachers'))->id);
    $subquery = "";
    if(($attempts && !$hascap)) {
        $subquery = "SELECT ques_att.questionid
                FROM {question_attempts} as ques_att
                JOIN {quiz_attempts} as qz_att
                on ques_att.questionusageid = qz_att.uniqueid
                WHERE qz_att.id = ".get_last_inprogress_quizattempt_id($USER->id,$quizchat->quiz);
    }
    // Get all messages that are rightfully yours
    $sqlquery =
        "SELECT DISTINCT
            qchm.id, 
            qchm.userid, 
            CASE 
                WHEN qchm.questionid IS NULL THEN qchm.receiverid
                WHEN qchm.questionid IS NOT NULL AND qchm.receiverid != 0 THEN qchm.receiverid
                WHEN qchm.questionid IS NOT NULL AND qchm.receiverid = 0 THEN ".QUIZCHAT_ADDRESS_QUESTION_GROUP."
            END as receiverid,
            qchm.groupid, 
            qchm.timestamp, 
            qchm.message,
            qchm.questionattemptid,
            qchm.questionid,
            CONCAT(u.lastname, ', ', u.firstname) AS fullname,
            CASE
            WHEN ra.userid IS NULL AND u.deleted = 0 THEN 'unenrolled'
            WHEN u.deleted = 1 THEN 'deleted' 
            WHEN u.suspended = 1 THEN 'suspended'
            ELSE COALESCE(qa.state, 'noattempt')
            END AS state,
            u.firstname,
            u.lastname,
            CASE 
                WHEN qchm.groupid IN (1, 2) THEN qchg.name 
                WHEN qchm.groupid = 0 AND qchm.questionid IS NULL THEN 
                    (SELECT CONCAT(u2.lastname, ', ', u2.firstname) 
                     FROM {user} AS u2 
                     WHERE u2.id = qchm.receiverid) 
                WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL AND qchm.receiverid = 0 THEN 
                    (SELECT CONCAT('".$grp_langstr."', ' ', ques.name) 
                     FROM {question} AS ques 
                     WHERE ques.id = qchm.questionid)
                WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL AND qchm.receiverid != 0 THEN
                    (SELECT CONCAT(u2.lastname, ', ', u2.firstname) 
                    FROM {user} AS u2 
                    WHERE u2.id = qchm.receiverid) 
            END AS rfullname,
            CASE 
                WHEN qchm.groupid IN (1, 2) THEN qchg.name 
                WHEN qchm.groupid = 0 AND qchm.questionid IS NULL THEN 
                    (SELECT u2.firstname
                     FROM {user} AS u2 
                     WHERE u2.id = qchm.receiverid)
                WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL THEN 
                    (SELECT CONCAT('".$grp_langstr."', ' ', ques.name) 
                     FROM {question} AS ques 
                     WHERE ques.id = qchm.questionid)
            END AS rfirstname,
            CASE 
                WHEN qchm.groupid IN (1, 2) THEN qchg.name 
                WHEN qchm.groupid = 0 AND qchm.questionid IS NULL THEN 
                    (SELECT u2.lastname
                     FROM {user} AS u2 
                     WHERE u2.id = qchm.receiverid) 
                WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL THEN 
                    (SELECT CONCAT('".$grp_langstr."', ' ', ques.name) 
                     FROM {question} AS ques 
                     WHERE ques.id = qchm.questionid)
            END AS rlastname
        FROM {block_quizchat_messages} AS qchm
        LEFT JOIN {user} AS u 
        ON qchm.userid = u.id
        LEFT JOIN {block_quizchat_group} AS qchg 
        ON qchg.id = qchm.groupid
        JOIN {block_quizchat} qch
        on qch.id = qchm.quizchatid
        JOIN {context} ctx 
        ON qch.course = ctx.instanceid
        LEFT JOIN {role_assignments} ra 
        ON u.id = ra.userid
        LEFT join {quiz_attempts} qa
        on (qa.userid = u.id) AND (qa.quiz = qch.quiz)
        WHERE qchm.quizchatid = " . $quizchat->id
        ." AND qchm.id > " . $most_recent_msg_id
        ."    AND ((qchm.receiverid = " . $USER->id ." OR qchm.groupid IN (".$allgrp_id.") OR qchm.userid = ".$USER->id.") "
        // If user is an instructor they may also poll messages sent to groupid = 2 (teachers group) or 0 (one to one messages: messages sent from teachers accounts)
        . ($hascap ? " OR qchm.groupid IN ( ".$teachersgrp_id.",0)" : "")
        . ((!$hascap && $attempts)? " OR (qchm.questionid IN ( ".$subquery.") and qchm.receiverid = 0 and qchm.groupid = 0)" : "")
        ."    AND ctx.instanceid = qch.course)
        ORDER BY qchm.timestamp ASC;";
    $msg_records = $DB->get_records_sql($sqlquery);

    foreach($msg_records as $id => $record){
        $receiverid = $record->receiverid;
        if ($record->groupid == $allgrp_id)
        {
            $receiverid = QUIZCHAT_ADDRESS_EVERYONE;
        }
        else if ($record->groupid == $teachersgrp_id)
        {
            $receiverid = QUIZCHAT_ADDRESS_INSTRUCTORS;
        }
        //get question info
        $questioninfo = $langstr_general;
        $quizattemptnr = " ";
        $question_info_user = $USER->id;
        $questionid = 0;
        if($hascap) {$question_info_user = $record->userid;}
        if(!is_null($record->questionattemptid)) {
            $info = get_questioninfo($record->questionattemptid, $hascap, $quizchat->id, $question_info_user, $langstr_attempt, $langstr_general);
            $questioninfo = $info['slotorder'];
            $quizattemptnr = $info['quizattemptnr'];
            if($hascap) {$questionid = $info['questionid'];}
            else {
                $qzatt_id = get_quizattid_by_quesattid($record->questionattemptid);
                //get slot url
                $attemptobj = quiz_create_attempt_handling_errors(intVal($qzatt_id), intVal(($quizchat->cmid)));
                $can_nav = $attemptobj->can_navigate_to(intVal($info['slotorder']));
                if($can_nav) {
                    $slot_url = $attemptobj->attempt_url(intVal($info['slotorder']));
                    $slot_url_str = $slot_url->out(false);
                    $slot_order = $info['slotorder'];
                    $questioninfo = "<a href=\"{$slot_url_str}\" class='questionref'\">$slot_order</a>";
                } else {
                    $questioninfo = $info['slotorder'];
                }
            }
        }
        if(!is_null($record->questionid)&&$hascap) {
            $q_name = get_question_name_by_id($record->questionid);
            $questionid = $record->questionid;
            //question link
            $questionpreviewurl = new \moodle_url('/question/bank/previewquestion/preview.php', ['cmid' => $quizchat->cmid, 'id' => $record->questionid]);
            $questionpreviewlink = $questionpreviewurl->out(false);
            $questioninfo = "<a href=\"{$questionpreviewlink}\" id='questionref_link_{$questionid}' class='questionref' onclick=\"window.open('{$questionpreviewlink}', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,width=600,height=600'); return false;\">$q_name</a>";
            
        }
        if(!is_null($record->questionid)&&!$hascap) {
            $question_info_user = $USER->id;
            $qzatt_id= get_last_inprogress_quizattempt_id($USER->id,$quizchat->quiz);
            $questionatt_id = get_questionattemptid_in_quizattempt($record->questionid, $qzatt_id);
            $info = get_questioninfo($questionatt_id, $hascap, $quizchat->id, $question_info_user, $langstr_attempt, $langstr_general);
            $slotorder_str = $info['slotorder'];
            $quizattemptnr = $info['quizattemptnr'];
            //get slot url
            $attemptobj = quiz_create_attempt_handling_errors(intVal($qzatt_id), intVal(($quizchat->cmid)));
            $can_nav = $attemptobj->can_navigate_to(intVal($slotorder_str));
            if($can_nav) {
                $slot_url = $attemptobj->attempt_url(intVal($slotorder_str));
                $slot_url_str = $slot_url->out(false);
                $questioninfo = "<a href=\"{$slot_url_str}\" class='questionref'\">$slotorder_str</a>";
            } else {
                $questioninfo = $slotorder_str;
            }
        }
        //date
        $timestamp_parts = explode(',', userdate($record->timestamp, $langstr_strftimerecentfull));
        $date_part = trim($timestamp_parts[0]) . ', ' . trim($timestamp_parts[1]);
        array_push($msgStruct['messages'], [
            'id' => $id,
            'userid' => $record->userid,
            'receiverid' => $receiverid,
            'groupid' => $record->groupid,
            'timestamp' => $record->timestamp,
            'date_part' => $date_part,
            'message' => $record->message,
            'fullname' => $record->fullname,
            'state' => $record->state,
            'firstname' => $record->firstname,
            'lastname' => $record->lastname,
            'picture' => get_user_pic_url($record->userid),
            'rfullname' => ((!$hascap && !is_null($record->questionid))? $langstr_all : $record->rfullname),
            'rfirstname'=> ((!$hascap && !is_null($record->questionid))? $langstr_all : $record->rfirstname),
            'rlastname' => ((!$hascap && !is_null($record->questionid))? $langstr_all : $record->rlastname),
            'questiontxt' => $questioninfo,
            'quizattempt' => $quizattemptnr,
            'questionid' => $questionid
        ]);
        if($record->groupid == $allgrp_id || !is_null($record->questionid))
        {
            $msgStruct['stats']['group'] ++;
        }
        else
        {
            $msgStruct['stats']['private'] ++;
        }
    }
    $msgStruct['stats']['msg_total'] = count($msgStruct['messages']);
    //get private conversations between the current logged in user and others
    if($hascap) {
        $dbfamily = $DB->get_dbfamily();
        $sqlquery_p_users =
            "SELECT DISTINCT
            u.id as userid, 
            u.firstname, 
            u.lastname, 
            CONCAT(u.lastname, ', ', u.firstname) AS fullname,
            u.picture, 
            CASE
                WHEN ra.userid IS NULL AND u.deleted = 0 THEN 'unenrolled'
                WHEN u.deleted = 1 THEN 'deleted' 
                WHEN u.suspended = 1 THEN 'suspended'
                ELSE COALESCE(qa.state, 'noattempt')
            END AS state, "
            .($dbfamily == 'postgres' ? "(SELECT STRING_AGG(qcm_sub.id::text, ', ')
            FROM (
                SELECT DISTINCT qcm.id, qcm.timestamp
                FROM {block_quizchat_messages} AS qcm
                WHERE (qcm.userid = u.id OR qcm.receiverid = u.id)
                AND qcm.quizchatid = ". $quizchat->id.
                " ORDER BY qcm.timestamp ASC
            ) AS qcm_sub) AS message_ids" 
            : " GROUP_CONCAT(DISTINCT qcm.id ORDER BY qcm.timestamp ASC SEPARATOR ', ') as message_ids")
            ." FROM 
            {user} as u
        JOIN
        (
            SELECT DISTINCT id as user_id
            FROM
            (
                SELECT 
                    CASE 
                        WHEN pmsgs.groupid = ".$teachersgrp_id." OR pmsgs.receiverid = ".$USER->id." THEN pmsgs.userid
                        ELSE pmsgs.receiverid
                    END as id
                FROM 
                (
                    SELECT *
                    FROM {block_quizchat_messages} as qchm
                    WHERE qchm.quizchatid = " . $quizchat->id
                    ."  AND ((qchm.receiverid = ".$USER->id." AND qchm.groupid IN (0)) OR (qchm.userid = ".$USER->id." AND qchm.groupid IN (0)) OR (qchm.groupid = ".$teachersgrp_id."))  
                    AND qchm.groupid NOT IN (".$allgrp_id.") 
                    AND ((qchm.questionid IS not NULL and qchm.receiverid != 0) or (qchm.questionid IS NULL and qchm.groupid in (0,".$teachersgrp_id.")))
                    ORDER BY qchm.timestamp ASC
                ) as pmsgs
                WHERE ((pmsgs.receiverid = ".$USER->id." OR pmsgs.userid = ".$USER->id." OR pmsgs.groupid = ".$teachersgrp_id."))
            ) AS userids
        ) as u_ids
        ON u.id = u_ids.user_id
        LEFT JOIN {role_assignments} AS ra 
            ON u.id = ra.userid
        LEFT JOIN {quiz_attempts} AS qa
            ON u_ids.user_id = qa.userid 
            AND qa.quiz = " . $quizchat->quiz
            ." AND qa.timestart = (
                SELECT MAX(qa_max.timestart)
                FROM {quiz_attempts} as qa_max
                WHERE qa_max.quiz = qa.quiz
                AND qa_max.userid = qa.userid
            )".($dbfamily == 'postgres' ? "WHERE qa.quiz IS NULL OR qa.quiz = " . $quizchat->quiz . 
            " GROUP BY u.id, u.firstname, u.lastname, u.picture, u.deleted, u.suspended, ra.userid, state;" 
        : "JOIN {block_quizchat_messages} as qcm
            ON ((qcm.userid = u.id AND (qcm.receiverid = ".$USER->id." OR qcm.groupid = ".$teachersgrp_id.")) OR qcm.receiverid = u.id) AND (qcm.groupid != ".$allgrp_id." AND ((qcm.questionid IS not NULL and qcm.receiverid != 0) or (qcm.questionid IS NULL and qcm.groupid in (0,".$teachersgrp_id."))))
            AND qcm.quizchatid = " . $quizchat->id
            ." WHERE qa.quiz IS NULL OR qa.quiz = " . $quizchat->quiz
            ." GROUP BY u.id, u.firstname, u.lastname, u.picture, state;");
        $p_users_records = $DB->get_records_sql($sqlquery_p_users);
        foreach($p_users_records as $id => $record){
            array_push($msgStruct['p_users'], [
                'userid' => $record->userid,
                'firstname' => $record->firstname,
                'lastname' => $record->lastname,
                'fullname' => $record->fullname,
                'picture' => get_user_pic_url($record->userid),
                'state' => $record->state,
                'message_ids' => $record->message_ids
            ]);
        }
        //get the group messages
        $groupedMessages = array();
        // $default_image_url = new moodle_url('/theme/image.php', array('theme' => 'boost', 'component' => 'core', 'image' => 'u/f2'));
        // $userimg->size = 0;
        // if(!isset($PAGE->context))
        // {
        //     $PAGE->set_context(context_system::instance());
        // }
        // $imgurl = $userimg->get_url($PAGE)->out(false);
        //$grp_pic = "";//$CFG->wwwroot . '/theme/image.php/boost/core/u/f2';//$default_image_url->out();
        foreach ($msgStruct['messages'] as $message) {
            if (($message['quizattempt'] == " " && $message['questionid'] > 0 && $message['receiverid'] == QUIZCHAT_ADDRESS_QUESTION_GROUP) || $message['groupid'] == $allgrp_id) {
                //$grp_pic = new pix_icon('g/g1', ($message['groupid'] == $allgrp_id?$langstr_all:$message['rfullname']));
                $questionId = $message['questionid'];
                if (!isset($groupedMessages[$questionId])) {
                    // Initialize entry for the first time we encounter this questionid
                    $groupedMessages[$questionId] = array(
                        'question_id' => $questionId,
                        'group_name' => ($message['groupid'] == $allgrp_id?$langstr_all:$message['rfullname']),
                        'picture' => " ",
                        'message_ids' => array()
                    );
                }
                // Append the message id to the list of message_ids for this questionid
                $groupedMessages[$questionId]['message_ids'][] = $message['id'];
            }
        }

        // Convert message_ids arrays to comma-separated strings
        foreach ($groupedMessages as &$group) {
            $group['message_ids'] = implode(',', $group['message_ids']);
        }

        // Add the grouped messages to the 'groups' array
        $msgStruct['groups'] = array_values($groupedMessages);

        $msgStruct['stats']['private'] = count($msgStruct['p_users']);
        $msgStruct['stats']['group'] = count($msgStruct['groups']);

    }
}
else
{
    $msgStruct['stats']['msg_total'] = -1;//chat is unavailable for the current student
}
    return $msgStruct;
}

 /**
 * Check whether the quizchat block is available. It checks whether the login user has started attempting the given quiz id(if student) or if the login user is teacher.
 * @param int $quizid quiz id
 * @return bool true if enabled and false otherwise
 */
function check_blockavailability($quizid)
{
    global $USER, $DB, $CFG;
    require_once($CFG->dirroot.'/mod/quiz/lib.php');
    $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quizid));
    //check sendall capability of the current user
    $hascap=check_sendallcap($quizchat);
    // Check if the user has any attempts for the quiz
    $attempts = quiz_get_user_attempts($quizid, $USER->id,'unfinished',true);
    $enableblock = false;
    if($attempts||$hascap){
        $enableblock = true;
    }
    return $enableblock;
}

/**
 * Create new message
 * @param int $quizchatid quizchat id
 * @param int $receiverid receiver id
 * @param string $messagetext msg text
 * @param string $groupid group id
 * @param int $questionattemptid question attempt id
 * @param int $questionid question attempt id
 * @return bool true if enabled and false otherwise
 */
function create_msg($quizchatid, $receiverid, $messagetext, $groupid, $questionattemptid, $questionid,  $senderid = null)
{
    global $USER, $DB;
    $sender_id = null;
    if(is_null($senderid)) {
        $sender_id = $USER->id;
    }
    else {
        $sender_id = $senderid;
    }
    $quizchat = $DB->get_record('block_quizchat', array('id' => $quizchatid));

    $hascap = null;
    //check sendall capability of the current user
    if(is_null($senderid)) {
        $hascap = check_sendallcap($quizchat);
    }
    else {
        $hascap = check_sendallcap($quizchat, $sender_id);
    }
    //get teachers group id
    $teachersgrp_id = intVal($DB->get_record('block_quizchat_group', array('name' => 'teachers'))->id);
    // Check if the user has any attempts for the quiz
    $enableblock = check_blockavailability($quizchat->quiz);
    // Sending to anyone other than instructor 'group'? Let's see if you may! or didn't attempt the quiz and tries to send msg
    if(($teachersgrp_id !== (int)$groupid && !$hascap)||(!$hascap && !$enableblock)){
        return -1;
    }
    $question_attemptid = null;
    if ($questionattemptid !== 0) {
        $question_attemptid = $questionattemptid;
    }
    $question_id = null;
    if ($questionid !== 0) {
        $question_id = $questionid;
    }
    if(!$hascap)
    {
        $question_id = null;//questionid is null in student messages. questionattemptid is used in that case.
    }
    else
    {
        $question_attemptid = null;//questionattemptid is null in teacher messages. questionid is used in that case.
        if(($groupid == 0) && ($receiverid != 0) && (!is_null($question_id))) {
            //check if the receiverid has sendall cap or not
            $receiver_hascap = check_sendallcap($quizchat, $receiverid);
            if(!$receiver_hascap) {
                //if question is selected and a specific user is also selected, question id should be saved if receiver has sendall-cap. Otherweise, questionattemptid should be saved.  
                //get the questionattemptid with (receiverid & question_id) and then save questionattemptid
                $attempt_query = "SELECT ques_a.id as questionattemptid, last_qa.attempt , ques_a.questionid, ques_a.slot, last_qa.layout
                FROM {question_attempts} as ques_a
                join (SELECT *
                        FROM {quiz_attempts}
                        WHERE userid = " .$receiverid. "
                        AND quiz = ".$quizchat->quiz."
                        ORDER BY timestart DESC
                        LIMIT 1) as last_qa 
                on last_qa.uniqueid = ques_a.questionusageid
                where ques_a.questionid = ".$question_id.";";
                $question_attempt_record = $DB->get_record_sql($attempt_query);
                $question_attemptid = $question_attempt_record->questionattemptid;
                $question_id = null;
            }
        }
    }
    $message = [
        "quizchatid" => $quizchatid,
        "userid" => $sender_id,
        "receiverid" => $receiverid,
        "message" => $messagetext,
        "groupid" => $groupid,
        "timestamp" => time(),
        "questionattemptid" => $question_attemptid,
        "questionid" => $question_id
    ];
    $msg_id = $DB->insert_record('block_quizchat_messages', $message);
    // Trigger the event.
    $event = \block_quizchat\event\message_sent::create(array(
        'objectid' => $msg_id,
        'contextid' => $quizchat->contextid,
        'other' => array(
            'blockinstanceid' => $quizchat->instanceid,
            'cmid' => $quizchat->cmid
        )
    ));
    $event->trigger();
    return $msg_id;
}
/**
 * Returns array  of question data for the last attempt of a specific user in a quiz including questionid, questionversion, teacherslotorder, questiontxt and studentquestionorder.
 * The last version of a question was taken into consideration.
 * @param int $senderid user id(the sender id, who refrenced a question using @q).
 * @param int $quizchatid quizchat id
 * @param string $searchtext text to search for in questions menu.
 * @param string $generalstring language string general
 * @param int $quizattemptid quizattempt id if exists(it should exist in case function call to get msgs from get_questioninfo function)
 * @return array question data: questionid, teacherslotorder, questionsummary and studentquestionorder
 */
function get_slotorder($senderid, $quizchatid, $searchtext, $generalstring, $quizattemptid = null)
{
   $questioninfo = [];
   $questions = ["questions" => []];
   global $DB, $USER, $CFG;
   require_once($CFG->dirroot . '/mod/quiz/locallib.php');
   $quizchat = $DB->get_record('block_quizchat', array('id' => $quizchatid));
   //$generalstring = get_string('student_question_general', 'block_quizchat');
   //check sendall capability of the current user
   $hascap=check_sendallcap($quizchat);
   //if the caller function is execute (question menu in student view is the caller), hide teacherslotorder - questionsummary - questionlink - questionattemptid.
   //otherwise show them
   $backtrace = debug_backtrace();
   $caller = $backtrace[2]['function'];
   $caller2 = $backtrace[1]['function'];//get_msgs
   if($searchtext ==="" || str_contains(strtolower($generalstring), strtolower($searchtext))) {
           array_push($questions['questions'], [
            'questionid'            => QUIZCHAT_GENERAL_QUESTION_ID,
            'teacherslotorder'      => 0,
            'questionsummary'           => $generalstring,
            'studentquestionorder'  => 0,
            'questionlink' => $generalstring,
            'questionattemptid' => 0,
            'questionname' => $generalstring
        ]);
    }
        $quizcontextid = $quizchat ->parentcontextid;
        $quizid = $quizchat ->quiz;
        //get the last attempt of the sender id in the specified quiz - in case question menu
        $lastattemptquery = "SELECT *
        FROM {quiz_attempts}
        WHERE userid = ".$senderid."
        AND quiz = ".$quizid."
        ORDER BY timestart DESC
        LIMIT 1";
        if(!is_null($quizattemptid))//get the attempt data of a specific quiz attempt - student
        {
            $lastattemptquery = "SELECT *
                                 FROM {quiz_attempts}
                                 WHERE id = ".$quizattemptid;
        }
        else if($hascap)//get the last attempts data of all users of a specific quiz - teacher
        {
            $lastattemptquery = "SELECT *
                                 FROM {quiz_attempts} qa
                                 WHERE qa.quiz = ".$quizid."
                                 AND qa.timestart = (
                                     SELECT MAX(qa_max.timestart)
                                     FROM {quiz_attempts} qa_max
                                     WHERE qa_max.quiz = qa.quiz
                                     AND qa_max.userid = qa.userid
                                 );";
        }
        $lastattemptdata=$DB->get_records_sql($lastattemptquery);
        if ($lastattemptdata) {
            foreach ($lastattemptdata as $qzattempt) {
                // Explode the layout string into an array of slot numbers
                $slotNumbers = explode(',', $qzattempt->layout);
                // Filter out the "0" entries, as they represent empty slots
                $slotNumbers = array_filter($slotNumbers, function($value) {
                    return $value !== '0';
                });
                // Initialize student order
                $studentOrder = 0;
                // Check if slot numbers are available
                if (!empty($slotNumbers)) {
                    // get the slot numbers with their order
                    foreach ($slotNumbers as $slotNumber) {
                        //get question id to each slot number
                        $questionquery = "SELECT qa.questionid AS id, qa.slot AS teacherslot, qa.questionsummary, qa.id as questionattemptid, qa.behaviour as questiontype
                        FROM {question_attempts} AS qa
                        JOIN {question_usages} AS qu ON qa.questionusageid = qu.id
                        WHERE qu.contextid = ".$quizcontextid." AND qa.slot = ".$slotNumber." AND qa.questionusageid = ".$qzattempt->uniqueid.";";
                        $questiondata = $DB->get_records_sql($questionquery);
                        if (!empty($questiondata)) {
                            foreach($questiondata as $id => $question){
                                if (strcmp($question->questiontype,"informationitem") != 0) { //if not description type
                                    //Hide questions data in student view, that is sent in get_questionslot service.
                                    //Only studentquestionorder and questionattemptid are sent
                                    //If the caller function is execute (question menu in student view is the caller), hide teacherslotorder - questionsummary - questionlink - questionid.
                                    //Otherwise show them
                                    $teacherorder = strval(QUIZCHAT_STUDENT_QUESTION_ID);//'-1'
                                    $summary = '-';
                                    $questionpreviewlink = '-';
                                    $qid = strval(QUIZCHAT_STUDENT_QUESTION_ID);//'-1'
                                    $q_name = '-';
                                    if($caller == 'get_questioninfo'||$caller2 == 'get_questioninfo'||$hascap) {
                                        $teacherorder = $question->teacherslot;
                                        $summary = $question->questionsummary;
                                        $questionpreviewurl = new \moodle_url('/question/bank/previewquestion/preview.php', ['cmid' => $quizchat->cmid, 'id' => $question->id]);
                                        $questionpreviewlink = $questionpreviewurl->out(false);
                                        $qid = $question->id;
                                        $q_name = get_question_name_by_id($qid);
                                    }
                                    if($searchtext!=="") {
                                        $searchtext = strtolower($searchtext);
                                        if (($searchtext === strval($studentOrder + 1)) && !$hascap) {//if student search by student question order
                                            array_push($questions['questions'], [
                                                'questionid'            => $qid,
                                                'teacherslotorder'      => $teacherorder,
                                                'questionsummary'           => $summary,
                                                'studentquestionorder'  => strval($studentOrder + 1),
                                                'questionlink' => $questionpreviewlink,
                                                'questionattemptid' => $question->questionattemptid,
                                                'questionname' => $q_name
                                            ]);
                                            break 2;
                                        }
                                        else if((strpos($q_name, $searchtext) !== false) && $hascap) {
                                            // Extract the questionid values into a separate array
                                            $questionIds = array_column($questions['questions'], 'questionid');

                                            // Check if $qid doesn't exist in $questionIds
                                            if (!in_array($qid, $questionIds) || $qid == -1) {
                                                array_push($questions['questions'], [
                                                    'questionid' => $qid,
                                                    'teacherslotorder' => $teacherorder,
                                                    'questionsummary' => $summary,
                                                    'studentquestionorder' => strval($studentOrder + 1),
                                                    'questionlink' => $questionpreviewlink,
                                                    'questionattemptid' => $question->questionattemptid,
                                                    'questionname' => $q_name
                                                ]);
                                            }
                                        }
                                    }
                                    else {
                                        // Extract the questionid values into a separate array
                                        $questionIds = array_column($questions['questions'], 'questionid');

                                        // Check if $qid doesn't exist in $questionIds
                                        if (!in_array($qid, $questionIds) || $qid == -1) {
                                            array_push($questions['questions'], [
                                                'questionid' => $qid,
                                                'teacherslotorder' => $teacherorder,
                                                'questionsummary' => $summary,
                                                'studentquestionorder' => strval($studentOrder + 1),
                                                'questionlink' => $questionpreviewlink,
                                                'questionattemptid' => $question->questionattemptid,
                                                'questionname' => $q_name
                                            ]);
                                        }
                                    }
                                }
                                else {// if descriptiontype question
                                    $studentOrder--;
                                }
                                
                            }
                        }
                        // Increment student order
                        $studentOrder++;
                    }
                }
            }
        }
        else {
            if($hascap) {
                $quizobj = quiz::create($quizchat->quiz, $USER->id);
                $quizobj->preload_questions();
                $quizobj->load_questions();
                $questionsdata = $quizobj->get_questions(null, false);
                $questionIds = array_keys($questionsdata);
                if (!empty($questionsdata)) {
                    foreach($questionsdata as $questionid => $question){
                        $teacherorder = $question->slot;
                        $summary = $question->questiontext;
                        $questionpreviewurl = new \moodle_url('/question/bank/previewquestion/preview.php', ['cmid' => $quizchat->cmid, 'id' => $questionid]);
                        $questionpreviewlink = $questionpreviewurl->out(false);
                        $qid = $questionid;
                        $q_name = $question->name;
                        if($searchtext!=="") {
                            if(strpos(strtolower($q_name), strtolower($searchtext)) !== false) {
                                
                                array_push($questions['questions'], [
                                    'questionid' => $qid,
                                    'teacherslotorder' => $teacherorder,
                                    'questionsummary' => $summary,
                                    'studentquestionorder' => -1,
                                    'questionlink' => $questionpreviewlink,
                                    'questionattemptid' => -1,
                                    'questionname' => $q_name
                                ]);

                            }
                        }
                        else {
                            array_push($questions['questions'], [
                                'questionid' => $qid,
                                'teacherslotorder' => $teacherorder,
                                'questionsummary' => $summary,
                                'studentquestionorder' => -1,
                                'questionlink' => $questionpreviewlink,
                                'questionattemptid' => -1,
                                'questionname' => $q_name
                            ]);
                        }
                    }
                }
            }
            
        }
   return $questions;
}

/**
 * Returns string of question info that will be displayed in message body
 * @param int $questionattemptid question attempt id
 * @param bool $has_sendallcap if student question info will be slot number, otherwise question name/title (will be implemented later)
 * @param int $quizchatid quizchat id
 * @param int $userid user id
 * @param string $quiz_attempt_txt language string attempt
 * @param string $generalstring language string general
 * @return array question info that will be displayed in message body
 */
function get_questioninfo($questionattemptid, $has_sendallcap, $quizchatid, $userid, $quiz_attempt_txt, $generalstring)
{
    global $DB;
    $slot = "";
    //$quiz_attempt_txt = get_string('quiz_attempt_txt', 'block_quizchat');
    //get slot number and display it as question info in student view
    $query = "SELECT slot, questionusageid, questionid
    FROM {question_attempts}
    WHERE id= ".$questionattemptid.";";
    $question_attempt_record = $DB->get_record_sql($query);
    $slot = $question_attempt_record->slot;
    $questionusageid = $question_attempt_record->questionusageid;
    $questionid = $question_attempt_record->questionid;
    $quizattempt_query = "SELECT id,attempt
    FROM {quiz_attempts}
    WHERE uniqueid= ".$questionusageid.";";
    $quizattempt_record = $DB->get_record_sql($quizattempt_query);
    $quizattemptid = $quizattempt_record->id;
    $quizattemptnr = $quizattempt_record->attempt;
    $slotorder = 0;
    if(!is_null($slot)) {
        //get slot order
        $questions_infos = get_slotorder($userid, $quizchatid, "", $generalstring, $quizattemptid);
        foreach ($questions_infos as $questions_info) {
            foreach ($questions_info as $question) {
                if($question['teacherslotorder'] === $slot)
                {
                    if(!$has_sendallcap) {
                        $slotorder = $question['studentquestionorder'];
                    } else {
                        //get slot number and display it as question info in student view
                        $q_name = get_question_name_by_id($question['questionid']);
                        $slotorder = "<a href=\"{$question['questionlink']}\" id='questionref_link_{$question['questionid']}' class='questionref' onclick=\"window.open('{$question['questionlink']}', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,width=600,height=600'); return false;\">$q_name</a>";
                    }
                    break 2;
                }
            }
        }
    }
    return array(
        'slotorder' => $slotorder,
        'quizattemptnr' => " - ".$quiz_attempt_txt." ".$quizattemptnr,
        'questionid' => $questionid
    );
}

/**
 * Returns string of question name 
 * @param int $questionid question id
 * @return string question name
 */
function get_question_name_by_id($questionid)
{
    global $DB;
    $q_name_query = "SELECT name
                        FROM {question}
                        WHERE id = ".$questionid.";";
    $q_name = $DB->get_field_sql($q_name_query);
    return $q_name;
}

/**
 * Returns the last inprogress quizattempt id of a specific user in a specific quiz
 * @param int $userid user id
 * @param int $quizid quiz id
 * @return int quizattempt id
 */
function get_last_inprogress_quizattempt_id($userid, $quizid)
{
    global $DB;
    $query = "SELECT id
                FROM {quiz_attempts} qa
                WHERE qa.quiz = ".$quizid
                ." AND qa.userid = ".$userid
                ." AND qa.state = '".quiz_attempt::IN_PROGRESS
                ."' AND qa.timestart = (
                    SELECT MAX(qa_max.timestart)
                    FROM {quiz_attempts} qa_max
                    WHERE qa_max.quiz = qa.quiz
                    AND qa_max.userid = qa.userid
                );";
    $qzatt_id = $DB->get_field_sql($query);
    return $qzatt_id;
}

/**
 * Returns questionattempt id in a specific quizattempt id with question id
 * @param int $questionid question id
 * @param int $quizattemptid quizattempt id
 * @return int questionattempt id
 */
function get_questionattemptid_in_quizattempt($questionid, $quizattemptid)
{
    global $DB;
    $query = "SELECT ques_att.id
                FROM {question_attempts} ques_att
                JOIN {quiz_attempts} qz_att
                ON ques_att.questionusageid = qz_att.uniqueid
                WHERE qz_att.id = " . $quizattemptid
                ." AND ques_att.questionid = ".$questionid;
    $questionatt_id = $DB->get_field_sql($query);
    return $questionatt_id;
}