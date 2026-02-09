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
namespace block_quizchat\privacy;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\transform;
defined('MOODLE_INTERNAL') || die();
class provider implements
\core_privacy\local\metadata\provider,
\core_privacy\local\request\core_userlist_provider,
\core_privacy\local\request\plugin\provider {

        /**
     * Returns metadata.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        
        
        $collection->add_database_table('block_quizchat', [
            'course'         => 'privacy:metadata:block_quizchat:course',
            'quiz'           => 'privacy:metadata:block_quizchat:quiz',
            'timecreated' => 'privacy:metadata:block_quizchat:timecreated'
        ], 'privacy:metadata:block_quizchat');

        $collection->add_database_table('block_quizchat_messages', [
            'quizchatid' => 'privacy:metadata:block_quizchat_messages:quizchatid',
            'userid'     => 'privacy:metadata:block_quizchat_messages:userid',
            'receiverid' => 'privacy:metadata:block_quizchat_messages:receiverid',
            'groupid'    => 'privacy:metadata:block_quizchat_messages:groupid',
            'message'    => 'privacy:metadata:block_quizchat_messages:message',
            'timestamp'  => 'privacy:metadata:block_quizchat_messages:timestamp',
            'questionattemptid' => 'privacy:metadata:block_quizchat_messages:questionattemptid',
            'questionid' => 'privacy:metadata:block_quizchat_messages:questionid',
            'mgroupid' => 'privacy:metadata:block_quizchat_messages:mgroupid',
            'mgroupingid' => 'privacy:metadata:block_quizchat_messages:mgroupingid',
            'gname' => 'privacy:metadata:block_quizchat_messages:gname'
        ], 'privacy:metadata:block_quizchat_messages');

        $collection->add_database_table('block_quizchat_templates', [
            'id' => 'privacy:metadata:block_quizchat_templates:id',
            'title'     => 'privacy:metadata:block_quizchat_templates:title',
            'message' => 'privacy:metadata:block_quizchat_templates:message',
            'type'    => 'privacy:metadata:block_quizchat_templates:type',
            'isenabled'    => 'privacy:metadata:block_quizchat_templates:isenabled',
            'timecreated'  => 'privacy:metadata:block_quizchat_templates:timecreated',
            'timemodified' => 'privacy:metadata:block_quizchat_templates:timemodified'
        ], 'privacy:metadata:block_quizchat_templates');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        $sql = "SELECT DISTINCT qc.contextid
        FROM {block_quizchat} qc
        JOIN {block_quizchat_messages} qcm
        ON qcm.quizchatid = qc.id
        WHERE qcm.userid = :userid";

        $params = [
            'userid' => $userid,
        ];
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_block::class)) {
            return;
        }

        $params = [
            'contextid' => $context->id,
        ];

        $sql = "SELECT DISTINCT qcm.userid
        FROM {block_quizchat} qc
        JOIN {block_quizchat_messages} qcm
        ON qcm.quizchatid = qc.id
        WHERE qc.contextid = :contextid;";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $contexts = $contextlist->get_contexts();
        foreach ($contexts as $context) {
            static::export_messages($context);
        }
    }

    /**
     * Export information about the most recently accessed items.
     *
     * @param  int $userid The user ID.
     * @param  \context $context The user context.
     */
    protected static function export_messages(\context $context) {
        global $USER, $DB;
        $params = [
            'contextid' => $context->id,
            'userid'    => $USER->id
        ];
        $deleted_langstr = get_string('deleted_langstr', 'block_quizchat');
        $sql = "SELECT DISTINCT qcm.id,  
                CASE 
                    WHEN qcm.groupid IS NULL AND (qcm.mgroupid = 0 OR qcm.mgroupingid = 0) THEN 
                            CONCAT(qcm.gname, ' (".$deleted_langstr.")')
                    WHEN qcm.groupid IS NULL AND (qcm.mgroupid != 0 OR qcm.mgroupingid != 0) THEN 
                            qcm.gname
                    WHEN qcm.groupid IS NOT NULL AND qcm.groupid != 0 THEN qcg.name 
                END as receivergroup,
                	u.username, qcm.message, qcm.timestamp
                FROM {block_quizchat} qc
                JOIN {block_quizchat_messages} qcm ON qcm.quizchatid = qc.id
                LEFT JOIN {user} u ON u.id = qcm.receiverid
                LEFT JOIN {block_quizchat_group} qcg ON qcm.groupid = qcg.id
                WHERE qc.contextid = :contextid
                AND qcm.userid = :userid
                ORDER BY qcm.timestamp ASC;";

        $rs = $DB->get_records_sql($sql, $params);
        $msgs = [];
        $msg = [];
        foreach ($rs as $record) {
            $msg = [
                'Message id' => format_string($record->id),
                'Receiver group' => (is_null($record->receivergroup)?'-':format_string($record->receivergroup)),
                'Receiver username' => (is_null($record->username)?'-':format_string($record->username)),
                'Message' => format_string($record->message),
                'Time' => transform::datetime($record->timestamp),
            ];
            array_push($msgs, (object)$msg);
        }

        $sql_templates = "SELECT DISTINCT 
                            t.id,
                            t.title,
                            t.template,
                            q.name AS quizname,
                            c.fullname AS coursename,
                            t.isenabled,
                            CASE 
                                WHEN t.isquizlevel = 0 THEN 'central'
                                WHEN t.isquizlevel = 1 THEN 'quiz'
                            END AS type,
                            t.timecreated,
                            t.timemodified
                        FROM {block_quizchat_templates} t
                        LEFT JOIN {block_quizchat_block_templates} bt 
                               ON bt.templateid = t.id
                        LEFT JOIN {block_quizchat} qc 
                               ON qc.id = bt.quizchatid 
                              AND qc.contextid = :contextid   
                        LEFT JOIN {quiz} q 
                               ON qc.quiz = q.id
                        LEFT JOIN {course} c 
                               ON c.id = qc.course
                        WHERE t.userid = :userid
                        ORDER BY t.timecreated ASC;";
        $rs_temps = $DB->get_records_sql($sql_templates, $params);
        $temps = [];
        $temp = [];
        foreach ($rs_temps as $record_temp) {
            if ($record_temp->type == 'quiz' && !is_null($record_temp->quizname) ) {
                $temp = [
                'Template id' => format_string($record_temp->id),
                'Template title' => format_string($record_temp->title),
                'Template message' => format_string($record_temp->template),
                'Type' => format_string($record_temp->type),
                'Created in quiz' => format_string($record_temp->quizname),
                'Created in course' => format_string($record_temp->coursename),
                'Is enabled' => ($record_temp->isenabled?'enabled':'disabled'),
                'Time created' => transform::datetime($record_temp->timecreated),
                'Time modified' => transform::datetime($record_temp->timemodified)
                ];
                array_push($temps, (object)$temp);
            }
            if ($record_temp->type == 'central') {
                $temp = [
                'Template id' => format_string($record_temp->id),
                'Template title' => format_string($record_temp->title),
                'Template message' => format_string($record_temp->template),
                'Type' => format_string($record_temp->type),
                'Is enabled' => ($record_temp->isenabled?'enabled':'disabled'),
                'Time created' => transform::datetime($record_temp->timecreated),
                'Time modified' => transform::datetime($record_temp->timemodified)
                ];
                array_push($temps, (object)$temp);
            }
        }

        if (!empty($msgs)) {
            $subcontext[] = get_string('msgs', 'block_quizchat');
            \core_privacy\local\request\writer::with_context($context)
                ->export_data($subcontext, (object) [
                    'messages' => $msgs,
                ]);
        }
        if (!empty($temps)) {
            $subcontext_temp[] = get_string('currenttemplatemessages', 'block_quizchat');
            \core_privacy\local\request\writer::with_context($context)
                ->export_data($subcontext_temp, (object) [
                    'templates' => $temps,
                ]);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_BLOCK) {
            return;
        }
        $msgselect = 'quizchatid in (select id from {block_quizchat} WHERE contextid = :contextid)';
        $params = [
            'contextid' => $context->id,
        ];
        $DB->delete_records_select('block_quizchat_messages', $msgselect, $params);
        $sql_templates = "tempmsgid in (SELECT DISTINCT 
                            t.id
                        FROM {block_quizchat_templates} t
                        JOIN {block_quizchat_block_templates} bt 
                               ON bt.templateid = t.id
                        JOIN {block_quizchat} qc 
                               ON qc.id = bt.quizchatid 
                              AND qc.contextid = :contextid)";
        $sql_blocktemplates = "blktempmsgid in (SELECT DISTINCT 
                            bt.id 
                        FROM {block_quizchat_block_templates} bt 
                        JOIN {block_quizchat} qc 
                               ON qc.id = bt.quizchatid 
                              AND qc.contextid = :contextid)";
        $DB->delete_records_select('block_quizchat_block_templates', $sql_blocktemplates, $params);
        $DB->delete_records_select('block_quizchat_templates', $sql_templates, $params);
        
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['contextid' => $context->id], $userinparams);
        $sql = "(quizchatid in (select id from {block_quizchat} WHERE contextid = :contextid)) AND (userid {$userinsql})";
        $DB->delete_records_select('block_quizchat_messages', $sql, $params);
        $sql_templates = "tempmsgid in (SELECT DISTINCT 
                            t.id
                        FROM {block_quizchat_templates} t
                        JOIN {block_quizchat_block_templates} bt 
                               ON bt.templateid = t.id
                        JOIN {block_quizchat} qc 
                               ON qc.id = bt.quizchatid 
                              AND qc.contextid = :contextid 
                        WHERE t.userid {$userinsql})";
        $sql_blocktemplates = "blktempmsgid in (SELECT DISTINCT 
                            bt.id 
                        FROM {block_quizchat_block_templates} bt 
                        JOIN {block_quizchat_templates} t ON bt.templateid = t.id
                        JOIN {block_quizchat} qc 
                               ON qc.id = bt.quizchatid 
                              AND qc.contextid = :contextid 
                         WHERE t.userid {$userinsql})";
        $DB->delete_records_select('block_quizchat_block_templates', $sql_blocktemplates, $params);
        $DB->delete_records_select('block_quizchat_templates', $sql_templates, $params);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $msgselect = '(quizchatid in (select id from {block_quizchat} WHERE contextid = :contextid)) and (userid = :userid)';
            $params = [
                'contextid' => $context->id,
                'userid' => $userid
            ];
            $DB->delete_records_select('block_quizchat_messages', $msgselect, $params);
            $sql_templates = "tempmsgid in (SELECT DISTINCT 
                                t.id
                            FROM {block_quizchat_templates} t
                            JOIN {block_quizchat_block_templates} bt 
                                   ON bt.templateid = t.id
                            JOIN {block_quizchat} qc 
                                   ON qc.id = bt.quizchatid 
                                  AND qc.contextid = :contextid 
                            WHERE t.userid = :userid)";
            $sql_blocktemplates = "blktempmsgid in (SELECT DISTINCT 
                                bt.id 
                            FROM {block_quizchat_block_templates} bt 
                            JOIN {block_quizchat_templates} t ON bt.templateid = t.id
                            JOIN {block_quizchat} qc 
                                   ON qc.id = bt.quizchatid 
                                  AND qc.contextid = :contextid 
                             WHERE t.userid = :userid)";
            $DB->delete_records_select('block_quizchat_block_templates', $sql_blocktemplates, $params);
            $DB->delete_records_select('block_quizchat_templates', $sql_templates, $params);
        }
    }

}
