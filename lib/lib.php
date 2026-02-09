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
define('QUIZCHAT_ADDRESS_M_GROUP', -3);
define('QUIZCHAT_GENERAL_QUESTION_ID', 0);
define('QUIZCHAT_STUDENT_QUESTION_ID', -1);
define('QUIZCHAT_POLL_TIMEOUT_MIN', 5);
define('QUIZCHAT_UNNOTIFY_TIMEOUT_MIN', 3);
define('QUIZCHAT_POLL_TIMEOUT_MAX', 60);
define('QUIZCHAT_UNNOTIFY_TIMEOUT_MAX', 60);
define('QUIZCHAT_MSG_LENGTH_MIN', 1);
define('QUIZCHAT_MSG_LENGTH_MAX', 5000);
define('QUIZCHAT_TEMP_MSG_LENGTH_MAX', 100);
define('QUIZCHAT_OPEN_BEFORE_MINS', 20);
use mod_quiz\local\reports\report_base;
use core\output\pix_icon;

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
 * Returns array  of users data including 'id','firstname','lastname','fullname','state','questionid','questionname'.
 * If the fetched user is enrolled in the given quizid, the 'state' field is the user most recent attempt state in that quiz from quiz_attempts table.
 * @param int $quizid quiz id.
 * @param string $searchText text to search for in the enrolled users and match it with user fullname.
 * @param int $questionid the selected question id.
 * @param string $general_txt language string general
 * @return array array of user details:'id','firstname','lastname','fullname','state','questionid','questionname'.
 */
function get_usersdata($quizid, $searchText, $questionid, $general_txt)
{
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/group/lib.php');

    $searchText = strtolower($searchText);
    $users = [];

    $quizchat = $DB->get_record('block_quizchat', ['quiz' => $quizid]);
    $coursecontext = \context_course::instance($quizchat->course);

    // Get enrolled and group-allowed user IDs using helper function
    $ids = get_group_filtered_user_ids($quizid);
    $quizobj = \mod_quiz\quiz_settings::create_for_cmid(intVal($quizchat->cmid));
    $cm = $quizobj->get_cm();
    $groupmode = groups_get_activity_groupmode($cm);
    $context = $quizobj->get_context();
    $aag = has_capability('moodle/site:accessallgroups', $context);
    //get grouping id if exist
    $ggid = get_grouping_id(intVal($quizchat->cmid));
    $allowedgroups = ($groupmode == VISIBLEGROUPS || $aag)
        ? groups_get_all_groups($cm->course, 0, $ggid, 'g.*', false, true)
        : groups_get_all_groups($cm->course, $USER->id, $ggid, 'g.*', false, true);
    $activegroup = groups_get_activity_group($cm, true, $allowedgroups);
    if((!empty($ids) && $groupmode) || !$groupmode || (!$activegroup && $groupmode)) {
        $grp_enabled = is_quiz_group_access_enabled(intVal($quizchat->cmid), intVal($quizchat->course));

    $block_config_ar = ((array) unserialize_object(base64_decode($DB->get_record('block_instances', array('id' => $quizchat->instanceid))->configdata)));
    $enableopenbefore = $block_config_ar['enableopenbefore'];
    $openbefore = !empty($block_config_ar['openbefore']) ? (int)$block_config_ar['openbefore']/ MINSECS : 0;
    $quiz = $DB->get_record('quiz', ['id' => $quizchat->quiz], '*', MUST_EXIST);
    $quizopentime = $quiz->timeopen;
    $quizclosetime = (int)$quiz->timeclose > 0 ? (int)$quiz->timeclose : (int)$quizopentime + ((int)$quiz->timelimit);
    $chattimeopen = null;
    if ($enableopenbefore == "1" && $quizopentime) {
        $chattimeopen = (int)$quizopentime - ((int)$openbefore * 60);
    }
        if ($questionid != QUIZCHAT_GENERAL_QUESTION_ID) {
            // If a specific question is selected, get latest attempt per user for that question
            $participants_query = "SELECT * FROM (
                SELECT qa.userid as id,
                        u.firstname,
                        u.lastname,
                        CONCAT(u.lastname, ' ', u.firstname) AS fullname,
                        CASE
                            WHEN u.deleted = 1 THEN 'deleted' 
                            WHEN u.suspended = 1 THEN 'suspended'
                            ELSE qa.state
                        END AS state, ques_a.questionid, q.name as questionname
                FROM {quiz_attempts} qa
                JOIN {question_attempts} ques_a
                ON qa.uniqueid = ques_a.questionusageid
                JOIN {question} q
                ON q.id = ques_a.questionid
                JOIN {user} u 
                ON qa.userid = u.id
                WHERE qa.quiz = ".$quizid.($grp_enabled?" AND qa.userid IN (". $ids.") ":" ").
                "AND qa.timestart = (
                    SELECT MAX(qa_max.timestart)
                    FROM {quiz_attempts} qa_max
                WHERE qa_max.quiz = qa.quiz"
                .(($enableopenbefore == "1" && $quizopentime)?" AND qa_max.timestart >= " . $chattimeopen . " AND qa_max.timefinish <= " . $quizclosetime  : "").
                " AND qa_max.userid = qa.userid".($grp_enabled ?" AND qa_max.userid IN (".$ids.") ":" ").
                    ")
                AND ques_a.questionid = ".$questionid.") as users_question_attempts
                WHERE LOWER(users_question_attempts.fullname) LIKE CONCAT('%', LOWER('".$searchText."'), '%')
                ORDER BY users_question_attempts.fullname ASC;";

            $users = $DB->get_records_sql($participants_query, ['search' => $searchText]);

        } else {
            // General (no specific question), show all enrolled with attempt state 
            $participants_query = "SELECT DISTINCT * FROM (
                SELECT
                    u.id AS id,
                    u.firstname,
                    u.lastname,
                    CONCAT(u.lastname, ' ', u.firstname) AS fullname,
                    CASE
                        WHEN ra.userid IS NULL AND u.deleted = 0 THEN 'unenrolled'
                        WHEN u.deleted = 1 THEN 'deleted' 
                    WHEN u.suspended = 1 THEN 'suspended' "
                    .(($enableopenbefore == "1" && $quizopentime)? "WHEN qzatt.timestart < " . $chattimeopen . " THEN 'noattempt' " : "")
                    ."ELSE COALESCE(qzatt.state, 'noattempt')
                    END AS state,
                    ".QUIZCHAT_GENERAL_QUESTION_ID." as questionid, '".$general_txt."' as questionname
                FROM
                    {user_enrolments} ue
                JOIN
                    {enrol} e ON ue.enrolid = e.id
                JOIN
                    {user} u ON ue.userid = u.id
                LEFT JOIN {role_assignments} ra 
                    ON u.id = ra.userid AND ra.contextid = ".$coursecontext->id."
                LEFT JOIN (
                    SELECT qa.*
                    FROM {quiz_attempts} qa
                    JOIN (
                        SELECT userid, MAX(timestart) AS last_attempt_time
                        FROM {quiz_attempts}
                        WHERE quiz = ".$quizid.($grp_enabled?" AND userid IN (". $ids.") ":" ").
                        "GROUP BY userid
                    ) AS latest_attempt 
                    ON qa.userid = latest_attempt.userid 
                    AND qa.timestart = latest_attempt.last_attempt_time
                ) AS qzatt ON qzatt.userid = u.id
                WHERE
                    e.courseid = ".$quizchat->course.($grp_enabled?" AND u.id IN (".$ids.")":" ").
            ") AS enrolled
            WHERE LOWER(enrolled.fullname) LIKE CONCAT('%', LOWER('".$searchText."'), '%')
            ORDER BY enrolled.fullname ASC;";

            $users = $DB->get_records_sql($participants_query, [
                'search' => $searchText,
                'courseid' => $quizchat->course,
                'ctxid' => $coursecontext->id,
                'general_txt' => $general_txt
            ]);
        }
    }

    return $users;
}

 /**
 * Retrieves a comma-separated string of user IDs for a given quiz based on
 * group visibility, availability conditions, and grouping configurations.
 *
 * This function:
 * - Handles visible/separate group modes
 * - Considers the user's capability to access all groups
 * - Uses availability API to detect group/grouping-based restrictions
 * - Supports dynamic filtering of users based on group access and availability conditions
 *
 * @param int $quizid The ID of the quiz
 * @return string Comma-separated user IDs of group participants eligible to access the quiz
 */
function get_group_filtered_user_ids(int $quizid) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/group/lib.php');

    $quizchat = $DB->get_record('block_quizchat', ['quiz' => $quizid]);
    if (!$quizchat) {
        return '';
    }

    $quizobj = \mod_quiz\quiz_settings::create_for_cmid(intVal($quizchat->cmid));
    $cm = $quizobj->get_cm();
    $context = $quizobj->get_context();

    $groupmode = groups_get_activity_groupmode($cm);
    $aag = has_capability('moodle/site:accessallgroups', $context);
    //get grouping id if exist
    $ggid = get_grouping_id(intVal($quizchat->cmid));
    $allowedgroups = ($groupmode == VISIBLEGROUPS || $aag)
        ? groups_get_all_groups($cm->course, 0, $ggid, 'g.*', false, true)
        : groups_get_all_groups($cm->course, $USER->id, $ggid, 'g.*', false, true);
    $users_f = [];
    if(!empty($allowedgroups)) {
        $activegroup = groups_get_activity_group($cm, true, $allowedgroups);
        
        $grp_enabled = is_quiz_group_access_enabled(intVal($quizchat->cmid),intVal($quizchat->course) );
        if ($grp_enabled) {
            $coursecontext = \context_course::instance($quizchat->course);
            $enrolled = get_enrolled_users($coursecontext);

            $info = new \core_availability\info_module($cm);
            $grouping_found = null;
            $children = null;
            // Extract groupids from availability conditions
            if ($ggid == 0) {
                if(!empty($cm->availability)) {
                    $tree = $info->get_availability_tree();
                $checker = new \core_availability\capability_checker($context);
                
                // Access protected 'children' using Reflection
                $rc = new ReflectionClass($tree);
                $prop = $rc->getProperty('children');
                $prop->setAccessible(true);
                $children = $prop->getValue($tree);
                
                $groupids = array_map(function($child) use ($DB) {
                    $rc = new ReflectionClass($child);
                    if ($rc->hasProperty('groupid')) {
                        $prop = $rc->getProperty('groupid');
                        $prop->setAccessible(true);
                        return $prop->getValue($child);
                    } elseif ($rc->hasProperty('groupingid')) {
                        $prop = $rc->getProperty('groupingid');
                        $prop->setAccessible(true);
                        $groupingid = $prop->getValue($child);
                        $groupinggroups = $DB->get_records('groupings_groups', ['groupingid' => $groupingid]);
                        return array_column((array)$groupinggroups, 'groupid');
                    }
                    return null;
                }, $children);
                $grouping_found = array_filter($children, fn($child) => $child instanceof \availability_grouping\condition);
                } else {
                    if($activegroup == 0) {
                        $groupids = array_column($allowedgroups, 'id');
                    }
                    else {
                        $groupids[] = (int)$activegroup;
                    }
                }
            } else {
                $groupids = array_column($allowedgroups, 'id');
            }

            $groupids = array_merge(...array_map(fn($g) => (array)$g, $groupids));
            $groupids = array_unique(array_map('intval', $groupids));



            if (in_array($activegroup, $groupids) || $activegroup == 0) {
                $validgroup = true;
                $grps = [];

                if ($ggid == 0 && empty($grouping_found)) {
                    //$grps[] =(!empty($cm->availability)? $children :($activegroup == 0? $allowedgroups:$activegroup));
                    if(!empty($cm->availability)) {
                        $grps = $children;
                    } else {
                        if ($activegroup == 0) {
                            $grps = $allowedgroups;
                        }
                        else {
                            $grps[] = $activegroup;
                        }
                    }
                } elseif ($ggid != 0) {
                    $grps = $allowedgroups;
                } elseif (!empty($grouping_found)) {
                    $grps = $groupids;
                }

                foreach ($grps as $child) {
                    $gid = 0;

                    if ($ggid == 0 && empty($grouping_found)) {
                        if(!empty($cm->availability)) {
                            $rc = new ReflectionClass($child);
                            if ($rc->hasProperty('groupid')) {
                                $prop = $rc->getProperty('groupid');
                                $prop->setAccessible(true);
                                $gid = $prop->getValue($child);
                            } else {
                                continue;
                            }
                        } else {
                            if(!empty($child->id)) {
                                $gid = $child->id;
                            } else {
                                $gid = $child;
                            }
                        }

                    } elseif ($ggid == 0 && !empty($grouping_found)) {
                        $gid = $child;
                    } else {
                        $gid = $child->id;
                    }

                    if ($activegroup == 0 || ($activegroup != 0 && $gid == $activegroup)) {
                        if ($activegroup == 0 && $ggid == 0 && empty($grouping_found)) {
                            if(!empty($cm->availability)) {
                                $result = $child->filter_user_list($enrolled, false, $info, $checker);
                            } else {
                                $childresult = groups_get_members_by_role((int)$gid, $quizchat->course);
                                if($childresult) {
                                    $usersByRole = array_map(function($role) {
                                        return isset($role->users) ? $role->users : [];
                                    }, $childresult);
                                    
                                    $result = array_merge(...$usersByRole);
                                } else {
                                    $result = [];
                                }
                                
                            }

                        } else {
                            $childresult = groups_get_members_by_role((int)$gid, $quizchat->course);
                            if($childresult) {
                                $usersByRole = array_map(function($role) {
                                    return isset($role->users) ? $role->users : [];
                                }, $childresult);
                                
                                $result = array_merge(...$usersByRole);
                            } else {
                                $result = [];
                            }
                        }

                        foreach ($result as $id => $user) {
                            if (!isset($users_f[$id])) {
                                $users_f[$id] = $user;
                            }
                        }

                        if ($activegroup != 0 && $gid == $activegroup) {
                            break;
                        }
                    }
                }
            }
        }
    }

    return implode(',', array_column($users_f, 'id'));
}

/**
 * Get grouping id if $cm->groupingid exists in DB otherwise 0
 *
 * @param int $cmid The course module ID.
 * @return int grouping id 
 */
function get_grouping_id(int $cmid) {
    global $DB;
    $cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
    if(intVal($cm->groupingid) > 0) {
        $grouping = $DB->get_record('groupings', array('id' => intVal($cm->groupingid)), '*', IGNORE_MISSING);
        if($grouping) {
            return intVal($cm->groupingid);
        }
        else {
            return 0;
        }
    }
    return 0;
}

/**
 * Determines whether group-based access is enabled for a quiz activity.
 *
 * @param int $cmid The course module ID.
 * @param int $courseid The course ID.
 * @return bool True if group mode is active and user has appropriate group access, false otherwise.
 */
function is_quiz_group_access_enabled(int $cmid, int $courseid): bool {
    global $USER;

    // Create quiz object and retrieve necessary components.
    $quizobj = \mod_quiz\quiz_settings::create_for_cmid($cmid);
    $cm = $quizobj->get_cm();
    $context = $quizobj->get_context();

    // Get group mode for the activity.
    $groupmode = groups_get_activity_groupmode($cm); // NOGROUPS, SEPARATEGROUPS, or VISIBLEGROUPS
    $aag = has_capability('moodle/site:accessallgroups', $context);

    //get grouping id if exist
    $ggid = get_grouping_id(intVal($cmid));
    // Determine allowed groups based on group mode and user capability.
    $allowedgroups = ($groupmode == VISIBLEGROUPS || $aag)
        ? groups_get_all_groups($cm->course, 0, $ggid, 'g.*', false, true)
        : groups_get_all_groups($cm->course, $USER->id, $ggid, 'g.*', false, true);

    // Get user's active group for this activity.
    $activegroup = groups_get_activity_group($cm, true, $allowedgroups);

    // Return true if group mode is active and user is in a valid group.
    return ($activegroup > 0 && $groupmode != NOGROUPS);
}

/**
 * Helper function to get group and grouping IDs for a quiz activity.
 *
 * @param int $cmid The course module ID.
 * @return array An array containing 'groupids' and 'groupingids'.
 */
function get_quiz_group_data($cmid) {
    global $DB, $USER;

    $quizobj = \mod_quiz\quiz_settings::create_for_cmid((int)$cmid);
    $cm = $quizobj->get_cm();
    $course = $quizobj->get_course();
    $context = $quizobj->get_context();
    $quizgroupmode = groups_get_activity_groupmode($cm);
    $aag = has_capability('moodle/site:accessallgroups', $context);
    $ggid = get_grouping_id(intVal($cmid));
    if ($quizgroupmode == VISIBLEGROUPS or $aag) {
        $allowedgroups = groups_get_all_groups($cm->course, 0, $ggid, 'g.*', false, true);
    } else {
        $allowedgroups = groups_get_all_groups($cm->course, $USER->id, $ggid, 'g.*', false, true);
    }

    $activegroup = groups_get_activity_group($cm, true, $allowedgroups);
    $groupids = [];
    $groupingids = [];

    if ($activegroup >= 0 && $quizgroupmode) {
        $info = new \core_availability\info_module($cm);
        if(!empty($cm->availability)) {
            $tree = $info->get_availability_tree();
            $checker = new \core_availability\capability_checker($context);

            $rc = new ReflectionClass($tree);
            $prop = $rc->getProperty('children');
            $prop->setAccessible(true);
            $children = $prop->getValue($tree);

            if ($ggid == 0) {
                foreach ($children as $child) {
                    $rc_child = new ReflectionClass($child);

                    if ($rc_child->hasProperty('groupid')) {
                        $groupidProp = $rc_child->getProperty('groupid');
                        $groupidProp->setAccessible(true);
                        $gid = $groupidProp->getValue($child);

                        if ((($activegroup != 0 && $gid == $activegroup) || $activegroup == 0) && $DB->record_exists('groups', ['id' => $gid])) {
                            $groupids[] = (int)$gid;
                        }
                    }

                    if ($child instanceof \availability_grouping\condition) {
                        if ($rc_child->hasProperty('groupingid')) {
                            $groupingidProp = $rc_child->getProperty('groupingid');
                            $groupingidProp->setAccessible(true);
                            $gid = $groupingidProp->getValue($child);

                            if ((($activegroup != 0 && $gid == $activegroup) || $activegroup == 0) && $DB->record_exists('groupings', ['id' => $gid])) {
                                $groupingids[] = (int)$gid;
                            }
                        }
                    }
                }
            }
            else {
                $groupingids[] = (int)$ggid;
            }
        } else {
            if ($ggid == 0) {
                if($activegroup == 0) {
                    $groupids = array_column($allowedgroups, 'id');
                }
                else {
                    $groupids[] = (int)$activegroup;
                }
            }
            else {
                $groupingids[] = (int)$ggid;
            }
        }
    } else {
        $groupids = array_column($allowedgroups, 'id');
    }

    return [
        'groupids' => array_values(array_unique(array_map('intval', $groupids))),
        'groupingids' => array_values(array_unique(array_map('intval', $groupingids))),
    ];
}




 /**
 * Check whether the selected group can access a specific quiz
 * @param int $quizid quiz id
 * @return bool an indicator whether the selected group can access a specific quiz
 */
function is_quiz_accessible_to_group( $quizid)
{
    global $DB,$USER,$CFG,$SESSION;
    require_once($CFG->dirroot . '/group/lib.php');
    $accessible = false;
    $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quizid));
    $curr_quizobj = \mod_quiz\quiz_settings::create_for_cmid((int)$quizchat->cmid);
    $curr_cm_obj = $curr_quizobj->get_cm();
    $curr_course_obj = $curr_quizobj->get_course();
    $curr_context_obj = $curr_quizobj->get_context();
    $quizgroupmode = groups_get_activity_groupmode($curr_cm_obj);
    $aag = has_capability('moodle/site:accessallgroups', $curr_context_obj);
    $ggid = get_grouping_id(intVal($quizchat->cmid));
    if ($quizgroupmode == VISIBLEGROUPS or $aag) {
        $allowedgroups = groups_get_all_groups($curr_cm_obj->course, 0, $ggid, 'g.*', false, true); // Any group in grouping.
    } else {
        // Only assigned groups.
        $allowedgroups = groups_get_all_groups($curr_cm_obj->course, $USER->id, $ggid, 'g.*', false, true);
    }
    $activegroup = groups_get_activity_group($curr_cm_obj, true, $allowedgroups);
    if($activegroup > 0){
        $coursecontext = \context_course::instance($quizchat->course);
        $enrolled = get_enrolled_users($coursecontext);
        $info = new \core_availability\info_module($curr_cm_obj);
        if(!empty($curr_cm_obj->availability)) {
            $tree = $info->get_availability_tree();
            $checker = new \core_availability\capability_checker($curr_context_obj);
            // Use ReflectionClass to access protected 'children'  
            $rc = new ReflectionClass($tree);
            $prop = $rc->getProperty('children');
            $prop->setAccessible(true);
            $byreflection_children = $prop->getValue($tree);
            if($ggid == 0)
            {
                // Extract all 'groupid' values using array_map
                $groupids = array_map(function($child) {
                    global $DB;
                    $rc_temp = new ReflectionClass($child);
                    if($rc_temp->hasProperty('groupid')) {
                        // Use reflection to access the protected 'groupid' property
                        $prop = $rc_temp->getProperty('groupid');
                        $prop->setAccessible(true);  // Make the protected property accessible
                        return $prop->getValue($child);  // Get the value of 'groupid'
                    }
                    else if($rc_temp->hasProperty('groupingid')){
                        // Use reflection to access the protected 'groupid' property
                        $prop = $rc_temp->getProperty('groupingid');
                        $prop->setAccessible(true);
                        $current_groupingid = $prop->getValue($child);
                        $groupings_groups = $DB->get_records('groupings_groups', ['groupingid' => $current_groupingid]);
                        $grps = array_column((array) $groupings_groups, 'groupid');
                        if(!empty($grps)) {
                            return $grps;
                        }
                    }
                }, $byreflection_children);
            }
            else {
                $groupids = array_column($allowedgroups,'id');
            }
        } else {
            $groupids = array_column($allowedgroups,'id');
        }
        
        $groupids = array_merge(...array_map(fn($g) => (array)$g, $groupids));
        $groupids = array_map('intval', $groupids);
        $groupids = array_unique($groupids);

        if(in_array($activegroup, $groupids) || $activegroup == 0) {
            $accessible = true;
        }
    }
    else if($quizgroupmode && $activegroup == 0) {
        $accessible = true;
    }
    return $accessible;
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
 function get_msgs($quizchatid,$most_recent_msg_id, $langstr_general, $langstr_group, $langstr_attempt, $langstr_all, $langstr_strftimerecentfull, $deleted_langstr) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot.'/mod/quiz/lib.php');
    require_once($CFG->dirroot . '/mod/quiz/locallib.php');
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
    $hascap = check_sendallcap($quizchat);
    // Check if the user has any attempts for the quiz
    $attempts = quiz_get_user_attempts($quizchat->quiz, $USER->id,'all',true);
    $in_progress_attempts = [];
    if(!empty($attempts) && !$hascap) {
        $in_progress_attempts = array_filter($attempts, function ($attempt) {
            return $attempt->state === 'inprogress';
        });
    }
    $enableblock = check_blockavailability($quizchat->quiz);
    $curr_quizobj = \mod_quiz\quiz_settings::create_for_cmid($quizchat->cmid);
    $curr_cm_obj = $curr_quizobj->get_cm();
    $curr_course_obj = $curr_quizobj->get_course();
    $curr_context_obj = $curr_quizobj->get_context();
    $quizgroupmode = groups_get_activity_groupmode($curr_cm_obj);
    $showallgroups = ($quizgroupmode == NOGROUPS) || has_capability('moodle/site:accessallgroups', $curr_context_obj);
if ($enableblock && ((!$hascap)|| ($hascap && $showallgroups))) {
    $allgrp_id = intVal($DB->get_record('block_quizchat_group', array('name' => 'all'))->id);
    $teachersgrp_id = intVal($DB->get_record('block_quizchat_group', array('name' => 'teachers'))->id);
    $subquery = "";
    if(($attempts && !$hascap && !empty($in_progress_attempts))) {
        $subquery = "SELECT ques_att.questionid
                FROM {question_attempts} as ques_att
                JOIN {quiz_attempts} as qz_att
                on ques_att.questionusageid = qz_att.uniqueid
                WHERE qz_att.id = ".get_last_inprogress_quizattempt_id($USER->id,$quizchat->quiz);
    }
    $allgrp_name_query = "SELECT name FROM {block_quizchat_group} WHERE id = ".$allgrp_id;
    $grp_fullname_query = "WHEN qchm.groupid IS NULL AND (qchm.mgroupid = 0 OR qchm.mgroupingid = 0) THEN 
                            CONCAT(qchm.gname, ' (".$deleted_langstr.")')
                           WHEN qchm.groupid IS NULL AND (qchm.mgroupid != 0 OR qchm.mgroupingid != 0) THEN 
                            qchm.gname";
    $grp_enabled = is_quiz_group_access_enabled(intVal($quizchat->cmid), intVal($quizchat->course));
    $userids = '';
    $grp_ids = [];
    $grping_ids = [];
    $grp_txt = "";
    if($grp_enabled) {
        // Get enrolled and group-allowed user IDs using helper function 
        $userids = get_group_filtered_user_ids(intVal($quizchat->quiz));
        $groupData = get_quiz_group_data($quizchat->cmid);
        $grp_ids = implode(',', $groupData['groupids']);
        $grping_ids = implode(',', $groupData['groupingids']);
        $grp_conditions = [];
        if (!empty($groupData['groupids'])) {
            $grp_conditions[] =  (!$hascap? "qchm.mgroupid IN (SELECT groupid FROM {groups_members} where userid = ".$USER->id." and groupid in ($grp_ids))" :"qchm.mgroupid IN ($grp_ids)");
        }
        if (!empty($groupData['groupingids'])) {
            $grp_conditions[] = (!$hascap? "(qchm.mgroupingid IN (SELECT DISTINCT gg.groupingid FROM {groups_members} gm JOIN {groupings_groups} gg ON gm.groupid = gg.groupid WHERE gm.userid = ".$USER->id.") OR qchm.mgroupid IN (select gg.groupid from {groupings_groups} gg join {groups_members} gm on gm.groupid = gg.groupid where gg.groupingid in ($grping_ids) and gm.userid = ".$USER->id."))" :"(qchm.mgroupingid IN ($grping_ids) OR qchm.mgroupid IN (select groupid from {groupings_groups} where groupingid in ($grping_ids)))");
        }
        if (!empty($grp_conditions)) {
            if(!$hascap) {
                if(!empty($in_progress_attempts)) {
                    $grp_txt = "OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . ") AND (qchm.questionid IN ( ".$subquery.")))"
                    . " OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . ") AND (qchm.questionid IS NULL))";
                }
                else {
                    $grp_txt = "OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . "))"
                    . " OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . ") AND (qchm.questionid IS NULL))";
                }
            }
            else {
                $grp_txt = (!empty($userids)? "OR (qchm.groupid IS NOT NULL AND qchm.questionid IS NOT NULL AND qchm.receiverid = 0) OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . "))"
                            :"OR (qchm.groupid IN (".$allgrp_id.") AND qchm.questionid IS NOT NULL AND qchm.receiverid = 0) OR (qchm.groupid IS NULL AND (" . implode(' OR ', $grp_conditions) . "))");
            }
            
        }
    }
    // Get all messages that are rightfully yours
    $sqlquery =
        "SELECT DISTINCT
        qchm.id, 
        qchm.userid, 
        CASE 
            WHEN qchm.questionid IS NULL AND qchm.groupid IS NOT NULL THEN qchm.receiverid
            WHEN qchm.questionid IS NOT NULL AND qchm.receiverid != 0 AND qchm.groupid IS NOT NULL THEN qchm.receiverid
            WHEN qchm.questionid IS NOT NULL AND qchm.receiverid = 0 AND qchm.groupid IS NOT NULL THEN ".QUIZCHAT_ADDRESS_QUESTION_GROUP." 
            WHEN qchm.groupid IS NULL AND qchm.receiverid = 0 THEN ".QUIZCHAT_ADDRESS_M_GROUP." 
        END as receiverid,
        CASE 
            WHEN qchm.groupid = ".$allgrp_id." AND qchm.mgroupid = 0  THEN ".$allgrp_id." 
            WHEN qchm.groupid is null THEN null
            WHEN qchm.groupid is not null THEN qchm.groupid
        END AS groupid, 
        qchm.timestamp, 
        qchm.message,
        qchm.questionattemptid,
        qchm.questionid,
        CONCAT(u.lastname, ', ', u.firstname) AS fullname,
        CASE
            WHEN ra.userid IS NULL AND u.deleted = 0 THEN 'unenrolled'
            WHEN u.deleted = 1 THEN 'deleted' 
            WHEN u.suspended = 1 THEN 'suspended' "
            .(!$hascap &&!empty($in_progress_attempts)?"ELSE COALESCE(qa.state, 'noattempt') ":"ELSE 'noattempt' ")
        ."END AS state,
        u.firstname,
        u.lastname,
        CASE 
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") THEN qchg.name 
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
                 WHERE u2.id = qchm.receiverid) ".$grp_fullname_query."
        END AS rfullname,
        CASE 
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") THEN qchg.name 
            WHEN qchm.groupid = 0 AND qchm.questionid IS NULL THEN 
                (SELECT u2.firstname
                 FROM {user} AS u2 
                 WHERE u2.id = qchm.receiverid)
            WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL THEN 
                (SELECT CONCAT('".$grp_langstr."', ' ', ques.name) 
                 FROM {question} AS ques 
                 WHERE ques.id = qchm.questionid) ".$grp_fullname_query."
        END AS rfirstname,
        CASE 
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") THEN qchg.name 
            WHEN qchm.groupid = 0 AND qchm.questionid IS NULL THEN 
                (SELECT u2.lastname
                 FROM {user} AS u2 
                 WHERE u2.id = qchm.receiverid) 
            WHEN qchm.groupid = 0 AND qchm.questionid IS NOT NULL THEN 
                (SELECT CONCAT('".$grp_langstr."', ' ', ques.name) 
                 FROM {question} AS ques 
                 WHERE ques.id = qchm.questionid) ".$grp_fullname_query."
        END AS rlastname,
        CASE
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") AND qchm.mgroupid IS NULL AND qchm.mgroupingid IS NULL THEN NULL 
            WHEN qchm.groupid = ".$allgrp_id." AND qchm.mgroupid = 0 THEN (".$allgrp_name_query.") 
            WHEN qchm.groupid = 0 THEN NULL 
            WHEN qchm.groupid IS NULL AND qchm.mgroupid > 0 THEN
                'group' 
            WHEN qchm.groupid IS NULL AND qchm.mgroupingid > 0 THEN
                'grouping'
        END AS mgrp_type,
        CASE 
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") THEN null 
            WHEN qchm.groupid = 0 THEN null
            WHEN qchm.groupid IS NULL AND (qchm.mgroupid = 0 OR qchm.mgroupingid = 0) THEN True
            WHEN qchm.groupid IS NULL AND (qchm.mgroupid > 0 OR qchm.mgroupingid > 0) THEN False
        END AS mgrp_deleted,
        CASE 
            WHEN qchm.groupid IN (".$allgrp_id.", ".$teachersgrp_id.") THEN null 
            WHEN qchm.groupid = 0 THEN null
            WHEN qchm.groupid IS NULL AND qchm.mgroupid > 0 THEN qchm.mgroupid 
            WHEN qchm.groupid IS NULL AND qchm.mgroupingid > 0 THEN qchm.mgroupingid
            WHEN qchm.groupid IS NULL AND (qchm.mgroupid = 0 OR qchm.mgroupingid = 0) THEN null
        END AS mgrp_id
    FROM {block_quizchat_messages} AS qchm
    LEFT JOIN {user} AS u 
        ON qchm.userid = u.id
    LEFT JOIN {block_quizchat_group} AS qchg 
        ON qchg.id = qchm.groupid 
    JOIN {block_quizchat} qch
        ON qch.id = qchm.quizchatid
    JOIN {context} ctx 
        ON qch.course = ctx.instanceid
    LEFT JOIN {role_assignments} ra 
        ON u.id = ra.userid " 
    .((!$hascap && $attempts && !empty($in_progress_attempts))||$hascap?
        "LEFT JOIN {quiz_attempts} qa
        ON (qa.userid = u.id) AND (qa.quiz = qch.quiz)":"")
    ."WHERE qchm.quizchatid = " . $quizchat->id 
    ." AND qchm.id > " . $most_recent_msg_id
    .((($hascap && !$grp_enabled) || !$hascap)?
        "    AND ((qchm.receiverid = " . $USER->id ." OR qchm.groupid IN (".$allgrp_id.") OR qchm.userid = ".$USER->id.") "
      : (!empty($userids)? 
      "    AND (((qchm.groupid is not null AND qchm.receiverid = " . $USER->id ." AND qchm.userid IN (".$userids.")) OR qchm.groupid IN (".$allgrp_id.") OR (qchm.groupid is not null AND qchm.userid = ".$USER->id." AND qchm.receiverid IN (".$userids."))) "
      : "    AND ((qchm.groupid IN (".$allgrp_id.")) ")
     )
    // If user is an instructor they may also poll messages sent to groupid = 2 (teachers group) or 0 (one to one messages: messages sent from teachers accounts)
    . ($hascap ? 
        (!$grp_enabled ?
             " OR qchm.groupid IN ( ".$teachersgrp_id.",0)" 
             : (!empty($userids)?" OR ((qchm.userid IN (".$userids.")) " . $grp_txt . ")":" ". $grp_txt)
        ) . " OR (qchm.groupid is NULL AND qchm.mgroupid = 0) OR (qchm.groupid is NULL AND qchm.mgroupingid = 0) "
        : (!$grp_enabled ? "" : $grp_txt ))
    . ((!$hascap && $attempts && !empty($in_progress_attempts) && !$grp_enabled)? " OR (qchm.questionid IN ( ".$subquery.") and qchm.receiverid = 0 and qchm.groupid = 0)" 
    : ((!$hascap && $attempts && !empty($in_progress_attempts) && $grp_enabled)? " OR (qchm.questionid IN ( ".$subquery.") and qchm.receiverid = 0 and qchm.groupid = 0)"
    :""))
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
            $quizattemptquery = "SELECT qz_a.id as quizattemptid, qz_a.uniqueid, qa.questionid, qz_a.userid, qa.slot
                                FROM {question_attempts} as qa
                                JOIN {quiz_attempts} as qz_a
                                ON qz_a.uniqueid = qa.questionusageid
                                WHERE qa.id = ".$record->questionattemptid;
            $quizattemptrecord = $DB->get_record_sql($quizattemptquery);
            $attemptobj = quiz_create_attempt_handling_errors(intVal($quizattemptrecord->quizattemptid), intVal(($quizchat->cmid)));
            $question_attempt_obj = $attemptobj->get_question_attempt(intVal($quizattemptrecord->slot));
            if($hascap) {
                $q_name = $question_attempt_obj->get_question()->name;
                $q_url = new \moodle_url('/question/bank/previewquestion/preview.php', ['cmid' => $quizchat->cmid, 'id' => $quizattemptrecord->questionid]);
                $questioninfo = "<a href=\"{$q_url->out(false)}\" id='questionref_link_{$quizattemptrecord->questionid}' class='questionref' title='{$q_name}' onclick=\"window.open('{$q_url->out(false)}', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,width=600,height=600'); return false;\">$q_name</a>";
                $quizattemptnr = ' - ' . $langstr_attempt . ' ' . $attemptobj->get_attempt_number();
                $questionid = intVal($quizattemptrecord->questionid);
            } else {
                $questioninfo = ($attemptobj->can_navigate_to(intval($quizattemptrecord->slot))? "<a href=\"{$attemptobj->attempt_url(intval($quizattemptrecord->slot))->out(false)}\" class='questionref' title='{$attemptobj->get_question_number(intVal($quizattemptrecord->slot))}'>{$attemptobj->get_question_number(intVal($quizattemptrecord->slot))}</a>" : $attemptobj->get_question_number(intVal($quizattemptrecord->slot)));
                $quizattemptnr = ' - ' . $langstr_attempt . ' ' . $attemptobj->get_attempt_number();
            }
        }
        if(!is_null($record->questionid) && $hascap) {
            $q_name = get_question_name_by_id($record->questionid);
            $questionid = intVal($record->questionid);
            //question link
            $questionpreviewurl = new \moodle_url('/question/bank/previewquestion/preview.php', ['cmid' => $quizchat->cmid, 'id' => $record->questionid]);
            $questionpreviewlink = $questionpreviewurl->out(false);
            $questioninfo = "<a href=\"{$questionpreviewlink}\" id='questionref_link_{$questionid}' class='questionref' title='{$q_name}' onclick=\"window.open('{$questionpreviewlink}', '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,width=600,height=600'); return false;\">$q_name</a>";
            
        }
        if(!is_null($record->questionid) && !$hascap) {
            $question_info_user = $USER->id;
            $qzatt_id= get_last_inprogress_quizattempt_id($USER->id,$quizchat->quiz);
            $questionatt_id = get_questionattemptid_in_quizattempt($record->questionid, $qzatt_id);//not used
            $attemptobj = quiz_create_attempt_handling_errors(intVal($qzatt_id), intVal(($quizchat->cmid)));
            $lastattemptquery = "SELECT *
                    FROM {quiz_attempts}
                    WHERE userid = ".$question_info_user."
                    AND quiz = ".$quizchat->quiz."
                    ORDER BY timestart DESC
                    LIMIT 1";
            $qzattempt = $DB->get_record_sql($lastattemptquery);
            $qubaid = intVal($qzattempt->uniqueid);
            $quba = \question_engine::load_questions_usage_by_activity($qubaid);
            $slots = $attemptobj->get_slots();
            // Use ReflectionClass to access protected 'questionattempts'  
            $rc = new ReflectionClass($quba);
            $prop = $rc->getProperty('questionattempts');
            $prop->setAccessible(true);
            $byreflection_quesattempts = $prop->getValue($quba);
            $q = array_values(array_map(
                fn($attempt) => [
                    'slot' => intVal($attempt->get_slot()),
                    'number' => $attemptobj->get_question_number(intVal($attempt->get_slot())), 
                    'link' => ($attemptobj->can_navigate_to(intval($attempt->get_slot()))? "<a href=\"{$attemptobj->attempt_url(intval($attempt->get_slot()))->out(false)}\" class='questionref' title='{$attemptobj->get_question_number(intVal($attempt->get_slot()))}'>{$attemptobj->get_question_number(intVal($attempt->get_slot()))}</a>" : $attemptobj->get_question_number(intVal($attempt->get_slot()))),
                    'questionattemptid' => intVal($attempt->get_database_id())
                ],
                array_filter($byreflection_quesattempts, fn($attempt) => $attemptobj->get_question_type_name(intVal($attempt->get_slot())) !== 'description' && intVal($attempt->get_question_id()) == intVal($record->questionid))
            ));
            $questioninfo = $q[0]['link'];
            $quizattemptnr = ' ';
            $questionid = intVal($record->questionid);
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
        $p_users_records = [];
        if(($grp_enabled && !empty($userids)) || (!$grp_enabled)) {
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
                        ."  AND ((qchm.receiverid = ".$USER->id." AND qchm.groupid IN (0)".($grp_enabled? " AND qchm.userid IN (".$userids.")" : "") .") OR (qchm.userid = ".$USER->id." AND qchm.groupid IN (0)".($grp_enabled? " AND qchm.receiverid IN (".$userids.")" : "") .") OR (qchm.groupid = ".$teachersgrp_id.($grp_enabled? " AND qchm.userid IN (".$userids.")" : "") ."))  
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
        }
        foreach($p_users_records as $id => $record) {
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
    $block_config_ar = ((array) unserialize_object(base64_decode($DB->get_record('block_instances', array('id' => $quizchat->instanceid))->configdata)));
    $enableopenbefore = $block_config_ar['enableopenbefore'];
    $openbefore = !empty($block_config_ar['openbefore']) ? (int)$block_config_ar['openbefore'] / MINSECS: 0;
    //$quiz = $DB->get_record('quiz', ['id' => $quizchat->quiz], '*', MUST_EXIST);
    $curr_quizsettobj = \mod_quiz\quiz_settings::create((int)$quizchat->quiz, $USER->id);
    // Use ReflectionClass to access protected attributes  
    $rc = new ReflectionClass($curr_quizsettobj);
    $prop = $rc->getProperty('quiz');
    $prop->setAccessible(true);
    $byreflection_quiz = $prop->getValue($curr_quizsettobj);
    $quiz = $byreflection_quiz;
    $quizopentime = $quiz->timeopen;
    $quizclosetime = (int)$quiz->timeclose > 0 ? (int)$quiz->timeclose : (int)$quizopentime + ((int)$quiz->timelimit);
    $opentime_exceeded = false;
    if ($enableopenbefore == "1" && $quizopentime && !$hascap) {
        
        $chattimeopen = (int)$quizopentime - ((int)$openbefore * 60);

        if (time() >= $chattimeopen && time() <= $quizclosetime) {
            $opentime_exceeded = true;
        }
    }
    $enableblock = false;
    if($attempts||$hascap||$opentime_exceeded){
        $enableblock = true;
    }
    return $enableblock;
}

/**
 * get the duration of how many minutes before the quiz starts will the chat open
 * @param int $quizid quiz id
 * @return int  how many minutes before the quiz starts will the chat open
 */
function get_open_duration($quizid): int
{
    global $DB;
    $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quizid));
    $block_config_ar = ((array) unserialize_object(base64_decode($DB->get_record('block_instances', array('id' => $quizchat->instanceid))->configdata)));
    $enableopenbefore = $block_config_ar['enableopenbefore'];
    $openbefore = 0;
    if($enableopenbefore) {
        $openbefore = !empty($block_config_ar['openbefore']) ? (int)$block_config_ar['openbefore'] / MINSECS: 0;
    }
    return $openbefore;
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
    $curr_quizobj = \mod_quiz\quiz_settings::create_for_cmid((int)$quizchat->cmid);
    $curr_cm_obj = $curr_quizobj->get_cm();
    $curr_course_obj = $curr_quizobj->get_course();
    $curr_context_obj = $curr_quizobj->get_context();
    $quizgroupmode = groups_get_activity_groupmode($curr_cm_obj);
    $aag = has_capability('moodle/site:accessallgroups', $curr_context_obj);
    //get grouping id if exist
    $ggid = get_grouping_id(intVal($quizchat->cmid));
    if ($quizgroupmode == VISIBLEGROUPS or $aag) {
        $allowedgroups = groups_get_all_groups($curr_cm_obj->course, 0, $ggid, 'g.*', false, true); // Any group in grouping.
    } else {
        // Only assigned groups.
        $allowedgroups = groups_get_all_groups($curr_cm_obj->course, $USER->id, $ggid, 'g.*', false, true);
    }

    $activegroup = groups_get_activity_group($curr_cm_obj, true, $allowedgroups);
    // | $quizgroupmode | $activegroup                        | $ggid                   | Mode                                                                                                          | block_quizchat_messages vars |
    // ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    // |   0            |       -                             |        -                                   | group/grouping mode is deactivated                                                                            | groupid selected id of participants menu (1 to all, 2 to teachers, 0 to question group or question attempt) - mgroupid, mgroupingid, gname = null |
    // |   "1"          |       0                             |        0                                   | group/grouping mode is activated - 'All participants' from groups menu is selected -  Grouping is deactivated | groupid = 1 (to all) - mgroupid = 0 - mgroupingid, gname = null
    // |   "1"          |       > 0 (int - selected group id) |        0                                   | group/grouping mode is activated - a group from groups menu is selected -  Grouping is deactivated            | groupid = null - mgroupid = selected group id - mgroupingid = null, gname = selected group name
    // |   "1"          |       0                             |        > 0 (string - selected grouping id) | group/grouping mode is activated - 'All participants' from groups menu is selected -  Grouping is activated   | groupid = null  - mgroupid = null - mgroupingid = selected grouping id - gname = selected grouping name
    // |   "1"          |       > 0 (int - selected group id) |        > 0 (string - selected grouping id) | group/grouping mode is activated - a group from groups menu is selected -  Grouping is activated              | groupid = null - mgroupid = selected group id - mgroupingid = null, gname = selected group name
    $ids = '';
    $coursecontext = \context_course::instance($quizchat->course);
    if($activegroup >= 0 && $quizgroupmode && $aag) { //group/grouping mode is activated
        $message = [
            "quizchatid" => $quizchatid,
            "userid" => $sender_id,
            "receiverid" => $receiverid,
            "message" => $messagetext,
            "groupid" => (($activegroup == 0 && intVal($ggid) == 0 && $receiverid <= 0)? 1 : ($receiverid <= 0 ? null:0)),
            "timestamp" => time(),
            "questionattemptid" => $question_attemptid,
            "questionid" => $question_id,
            "mgroupid" => ($activegroup > 0 && $receiverid <= 0 ? $activegroup : (($activegroup == 0 && intVal($ggid) == 0)? 0 :null)),
            "mgroupingid" => (($activegroup == 0 && intVal($ggid) > 0 && $receiverid <= 0) ? intVal($ggid) : null),
            "gname" => ($activegroup > 0 && $receiverid <= 0 ? $DB->get_record('groups',['id' => $activegroup])->name: (($activegroup == 0 && intVal($ggid) > 0 && $receiverid <= 0) ? $DB->get_record('groupings',['id' => intVal($ggid)])->name : null))
        ];
        $msgid = $DB->insert_record('block_quizchat_messages', $message);
        // Trigger the event.
        $event = \block_quizchat\event\message_sent::create(array(
            'objectid' => $msgid,
            'contextid' => $quizchat->contextid,
            'other' => array(
                'blockinstanceid' => $quizchat->instanceid,
                'cmid' => $quizchat->cmid
            )
        ));
        $event->trigger();
        return $msgid;
    }
    else {
        $message = [
            "quizchatid" => $quizchatid,
            "userid" => $sender_id,
            "receiverid" => $receiverid,
            "message" => $messagetext,
            "groupid" => $groupid,
            "timestamp" => time(),
            "questionattemptid" => $question_attemptid,
            "questionid" => $question_id,
            "mgroupid" => null,
            "mgroupingid" => null,
            "gname" => null
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
}

/**
 * Returns array  of question data for the last attempt of a specific user in a quiz including questionid, questionversion, teacherslotorder, questiontxt and studentquestionorder.
 * The last version of a question was taken into consideration.
 * @param int $senderid user id(the sender id, who refrenced a question using @q).
 * @param int $quizchatid quizchat id
 * @param string $searchtext text to search for in questions menu.
 * @param string $generalstring language string general
 * @return array question data: questionid, teacherslotorder, questionsummary and studentquestionorder
 */
function get_slotorder($senderid, $quizchatid, $searchtext, $generalstring)
{
   $questioninfo = [];
   $questions = ["questions" => []];
   global $DB, $USER, $CFG;
   require_once($CFG->dirroot . '/mod/quiz/locallib.php');
   $quizchat = $DB->get_record('block_quizchat', array('id' => $quizchatid));
   //check sendall capability of the current user
   $hascap = check_sendallcap($quizchat);
   //if the caller function is execute (question menu in student view is the caller), hide teacherslotorder - questionsummary - questionlink - questionattemptid.
   if($searchtext ==="" || str_contains(strtolower($generalstring), strtolower($searchtext))) {
           array_push($questions['questions'], [
            'questionid'            => QUIZCHAT_GENERAL_QUESTION_ID,
            'teacherslotorder'      => 0,
            'questionsummary'           => $generalstring,
            'studentquestionorder'  => '0',
            'questionlink' => $generalstring,
            'questionattemptid' => 0,
            'questionname' => $generalstring
        ]);
    }
    $quizcontextid = $quizchat ->parentcontextid;
    $quizid = $quizchat ->quiz;
    if($hascap) {// Questions menu teacher case
        $txt = strtolower($searchtext);
        $questions_per_quiz_query = "SELECT DISTINCT qa.questionid AS id, q.name, q.questiontext
            FROM {question_attempts} AS qa
            JOIN {quiz_attempts} AS qza 
            ON qa.questionusageid = qza.uniqueid
            JOIN {question} AS q
            ON q.id = qa.questionid
            WHERE qza.quiz = ".$quizid
            //." AND qza.state = '".quiz_attempt::IN_PROGRESS
            ." AND qza.timestart = (
                                SELECT MAX(qa_max.timestart)
                                FROM {quiz_attempts} qa_max
                                WHERE qa_max.quiz = qza.quiz
                                AND qa_max.userid = qza.userid
                            )
            AND qa.behaviour <> 'informationitem'
            AND LOWER(q.name) LIKE '%{$txt}%'
            ORDER BY q.name;";
        $questions_per_quiz = $DB->get_records_sql($questions_per_quiz_query);
        $real_questions = array_values(array_map(fn($q) => [
            'questionid' => (int)$q->id,
            'teacherslotorder' => -2,
            'questionsummary' => $q->questiontext,
            'studentquestionorder' => -2,
            'questionlink' => "-",
            'questionattemptid' => 0,
            'questionname' => $q->name
        ], $questions_per_quiz));
        $questions['questions'] = array_merge($questions['questions'], $real_questions);
    }
    else {// Questions menu student case
        require_once($CFG->dirroot.'/mod/quiz/lib.php');
        $lastattemptquery = "SELECT *
        FROM {quiz_attempts}
        WHERE userid = ".$senderid."
        AND quiz = ".$quizid."
        AND state = 'inprogress'
        ORDER BY timestart DESC
        LIMIT 1";
        $qzattempt = $DB->get_record_sql($lastattemptquery);
        if($qzattempt) {
            $attemptobj = quiz_create_attempt_handling_errors(intVal($qzattempt->id), intVal(($quizchat->cmid)));
            $qubaid = intVal($qzattempt->uniqueid);
            $quba = \question_engine::load_questions_usage_by_activity($qubaid);
            $slots = $attemptobj->get_slots();
            // Use ReflectionClass to access protected 'questionattempts'  
            $rc = new ReflectionClass($quba);
            $prop = $rc->getProperty('questionattempts');
            $prop->setAccessible(true);
            $byreflection_quesattempts = $prop->getValue($quba);
            $real_questions = array_values(array_map(
                fn($attempt) => [
                    'questionid' => -1,
                    'teacherslotorder' => -1,
                    'questionsummary' => "-",
                    'studentquestionorder' => $attemptobj->get_question_number(intVal($attempt->get_slot())), 
                    'questionlink' => "-",
                    'questionattemptid' => intVal($attempt->get_database_id()),
                    'questionname' => "-"
                ],
                array_filter($byreflection_quesattempts, fn($attempt) => $attemptobj->get_question_type_name(intVal($attempt->get_slot())) !== 'description' && stristr($attemptobj->get_question_number(intVal($attempt->get_slot())), strtolower($searchtext)))
            ));
            //questions sorting
            array_multisort(array_column($real_questions, 'studentquestionorder'), SORT_ASC, $real_questions);
            $questions['questions'] = array_merge($questions['questions'], $real_questions);
        }
    }
   return $questions;
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
                ." AND qa.state = '".\mod_quiz\quiz_attempt::IN_PROGRESS
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

/**
 * Create new template message or update a specific one
 * @param string $title template message title
 * @param string $template template message body
 * @param bool $isenabled whether this template is enabled for site-level use
 * @param bool $isquizlevel whether this template is for site-level or block-level use
 * @param int $templateid template id in case of edit template and null if create template
 * @param int $quizchatid quizchatid in case of add template in block and null otherwise
 * @return int $templateid templateid
 */
function create_temp_msg($title, $template, $isenabled, $isquizlevel, $templateid = null, $quizchatid = null)
{
    global $USER, $DB;
    $tempid=0;
    if($templateid > 0) {//edit template
        if(!$isquizlevel && $quizchatid > 0) {// edit central temp msg in a block
            // Fetch the record (ensure it exists)
            $bt_record = $DB->get_record('block_quizchat_block_templates', ['templateid' => $templateid, 'quizchatid' => $quizchatid], '*');
            if ($isenabled) {
                if($bt_record) {
                    $deleted_bt = $DB->delete_records('block_quizchat_block_templates', ['id' => (int)$bt_record->id]);
                }
            } else {
                if(!$bt_record) {
                    $blk_temp = [
                        "templateid" => $templateid,
                        "quizchatid" => $quizchatid,
                        "isexcluded" => true
                    ];
                    $blk_temp_id = $DB->insert_record('block_quizchat_block_templates', $blk_temp);
                }
            }
            $tempid = $templateid;
        } else {
            //edit central temp msg in admin settings or edit quiz temp msg in block
            $temp = (object)[
                "id" => $templateid,
                "title" => $title,
                "template" => $template,
                "isenabled" => $isenabled,
                "userid" => $USER->id,
                "timemodified" => time()
            ];
            $tempid = $DB->update_record('block_quizchat_templates', $temp);
        }
    } else {//create new template
        $temp = [
            "title" => $title,
            "template" => $template,
            "isenabled" => $isenabled,
            "isquizlevel" => $isquizlevel,
            "type" => 'msg', //"msg= predefined message in templates menu, ques= predefined topic in questions menu"
            "userid" => $USER->id,
            "timecreated" => time(),
            "timemodified" => time()
        ];
        $tempid = $DB->insert_record('block_quizchat_templates', $temp);

        if($quizchatid > 0) {
            $blk_temp = [
                "templateid" => $tempid,
                "quizchatid" => $quizchatid
            ];
            $blk_temp_id = $DB->insert_record('block_quizchat_block_templates', $blk_temp);
        }
    }
    return $tempid;
}

/**
 * @param int $templateid template id in case of get one template and null if get all templates
 * @param bool $onlyenabled true if only enabled templates to be retrieved
 * @param int $quizchatid quizchat id in case of get one template or all templates of a quizchat block
 * @param string $partial_name Partial name of template message title
 * @param bool $centraltempflag true if central template message are in use
 * Get one template msg data or get all template messages of type 'msg'
 * @return array List of formatted rows ready for display.
 */
function get_template_messages( $partial_name, $templateid = null, $onlyenabled= null, $quizchatid = null, $centraltempflag =null): array {
    global $DB, $CFG, $OUTPUT, $PAGE;
    require_once($CFG->dirroot . '/lib/moodlelib.php');
    $sql="";
    if(is_null($quizchatid) && !$centraltempflag)//get template messages for admin settings (site level template messages)
    {
        $sql = "SELECT t.id, t.title, t.template, t.isenabled, t.isquizlevel,
                   t.timemodified, t.userid,  
                   CASE 
                   WHEN t.userid = 0  THEN 'Auto Generated'
                   WHEN t.userid != 0  THEN CONCAT(u.lastname, ', ', u.firstname) 
               END as fullname 
              FROM {block_quizchat_templates} t
         LEFT JOIN {user} u ON u.id = t.userid
         LEFT JOIN {block_quizchat_block_templates} bt 
               ON bt.templateid = t.id" 
               //AND bt.quizchatid = ".$quizchatid
            ." WHERE t.type = :type AND t.isquizlevel = 0 "
             .($templateid > 0 ? " AND t.id = ".$templateid." ":"")
             .($onlyenabled ? " AND t.isenabled = ".$onlyenabled." ":"")
          ."AND LOWER(t.title) LIKE '%{$partial_name}%' ORDER BY t.timemodified DESC";
    }
    else {//get template messages in a block settings (site level template messages that are not excluded in block and enabled in site level +  quiz level template messages)
        if(!is_null($quizchatid) && $centraltempflag && !$onlyenabled) {
            $sql = "SELECT t.id, t.title, t.template,
             CASE 
                   WHEN bt.isexcluded IS NULL THEN 1
                   WHEN bt.isexcluded = 1  THEN 0
               END AS isenabled, 
               t.isquizlevel,
                           t.timemodified, t.userid,  CONCAT(u.lastname, ', ', u.firstname) AS fullname
                      FROM {block_quizchat_templates} t
                 LEFT JOIN {user} u ON u.id = t.userid
                 LEFT JOIN {block_quizchat_block_templates} bt 
               ON bt.templateid = t.id 
               AND bt.quizchatid = ".$quizchatid
                     ." WHERE t.type = :type AND t.isquizlevel = 0 AND t.isenabled = true "
                     //.($onlyenabled ? " AND t.isenabled = ".$onlyenabled." ":"")
                  ."AND LOWER(t.title) LIKE '%{$partial_name}%' ORDER BY t.timemodified DESC";
        } else {
            $sql =
          "SELECT 
               t.id, 
               t.title, 
               t.template,
               t.isenabled,
               t.isquizlevel,
               t.timemodified, 
               t.userid,  
               CASE 
                   WHEN t.isquizlevel = 0  THEN 'Admin'
                   WHEN t.isquizlevel != 0  THEN CONCAT(u.lastname, ', ', u.firstname)
               END as fullname
           FROM {block_quizchat_templates} t
           LEFT JOIN {user} u 
               ON u.id = t.userid
           LEFT JOIN {block_quizchat_block_templates} bt 
               ON bt.templateid = t.id 
               AND bt.quizchatid = ".$quizchatid
            ." WHERE 
               t.type = :type
               AND (
                   -- Case 1: linked to this block
                   (bt.id IS NOT NULL AND bt.isexcluded = 0"
                   .($onlyenabled ? " AND t.isenabled = ".$onlyenabled." ":"")
                   .")"
                   .($centraltempflag?" OR (t.isquizlevel = 0 AND t.isenabled = 1 AND bt.id IS NULL)":"")//-- Case 2: global quiz-level template, enabled, and not linked to this block"
                   .")"
               .($templateid > 0 ? " AND t.id = ".$templateid." ":"")
               //.($centraltempflag > 0 ? "":" AND t.isquizlevel = 1 ")
           ." AND LOWER(t.title) LIKE '%{$partial_name}%' ORDER BY t.timemodified DESC";
        }
    }

    $records = $DB->get_records_sql($sql, ['type' => 'msg']);
    $rows = [];
    $action_txt = '';
    if(!isset($PAGE->context))
    {
        $PAGE->set_context(context_system::instance());
    }
    foreach ($records as $rec) {
        // Enabled flag
        $isenabled = $rec->isenabled ? get_string('table_enabled_settings', 'block_quizchat') : get_string('table_disabled_settings', 'block_quizchat');
        // Creator full name
        $username = $rec->fullname;

        // Format date
        $timemodified = !empty($rec->timemodified)
            ? userdate($rec->timemodified, get_string('strftimedatetime', 'langconfig')) // '%d %B %Y, %I:%M %p'
            : '-';

        // Action links
        if(($rec->isquizlevel && !is_null($quizchatid)) || (is_null($quizchatid) && !$onlyenabled) ) {//if quiz/block level template and get_template_messages is called from block settings - or - if get_template_messages is called from block settings
            $editurl = '#';
            $deleteurl = '#';
            $editiconhtml = $OUTPUT->render(new pix_icon('t/edit', get_string('btn_edit', 'block_quizchat')));
            $editlink = html_writer::link(
                $editurl,
                $editiconhtml,
                [
                    'class' => 'edit_btn',
                    'data-id' => $rec->id,
                    'href' => '#'
                ]); 
            $deleteiconhtml = $OUTPUT->render(new pix_icon('t/delete', get_string('btn_delete', 'block_quizchat')));
            $deletelink = html_writer::link(
                $deleteurl,
                $deleteiconhtml,
                [
                    'class' => 'delete_btn',
                    'data-id' => $rec->id,
                    'href' => '#'
                ]
            );
            $action_txt = $editlink . ' ' . $deletelink;
        } else {//if site level template and get_template_messages is called from block settings
            $enablehtml = html_writer::div('<div class="form-check mb-3" style="width:50%; text-align:left;">
                        <input type="checkbox" id="enable_chkbx_' . $rec->id . '" class="enable_cent_temp_chkbx" data-id="' . $rec->id . '" ' . ($rec->isenabled?'checked':'') . '>
                        <label for="enable_chkbx_' . $rec->id . '" class="enable_cent_temp_label">'
                            . get_string('enabletemplate_sitelevel', 'block_quizchat') .
                        '</label>
                    </div>');
            /* link(
                $editurl,
                $editiconhtml,
                [
                    'class' => 'edit_btn',
                    'data-id' => $rec->id,
                    'href' => '#'
                ]);  */
            $action_txt = $enablehtml;
            $username = "Admin";
        }
        
        // Add to table rows
        $rows[] = [
            $action_txt,
            '<span class="title-truncate" title="'.$rec->title.'">'.$rec->title.'</span>',
            '<span class="txt-truncate" title="'.$rec->template.'">'.$rec->template.'</span>',
            $isenabled,
            $username,
            $timemodified,
            $rec->id,
            $rec->isenabled
        ];
    }

    return $rows;
}

/**
 * @param int $templateid template id 
 * Delete template message
 * @return bool success or fail
 */
function delete_template_msg($templateid): bool {
    global $DB;
    $deleted_bt = $DB->delete_records('block_quizchat_block_templates', ['templateid' => $templateid]);
    $deleted = $DB->delete_records('block_quizchat_templates', ['id' => $templateid]);
    
    return $deleted;
}
