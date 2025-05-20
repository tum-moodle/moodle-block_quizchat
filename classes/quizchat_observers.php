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
namespace block_quizchat;
defined('MOODLE_INTERNAL') || die();


class quizchat_observers {

    /**
     * Attempt deleted event, handle Quizchat messages related to them
     *
     * @param \mod_quiz\event\attempt_deleted $event
     */
    public static function quiz_attempt_deleted(\mod_quiz\event\attempt_deleted $event) {
        global $DB;
        $sqlquery = "SELECT DISTINCT id FROM {block_quizchat_messages} WHERE questionattemptid NOT IN (SELECT id FROM {question_attempts}) AND questionattemptid IS NOT NULL;";
        $msgs = $DB->get_records_sql($sqlquery);
        if($msgs) {
            foreach($msgs as $msg){
                $DB->delete_records('block_quizchat_messages', ['id' => $msg->id]);
            }
            \core\notification::info(get_string('attempt_deleted_notification', 'block_quizchat'));
        }
    }

    /**
     * Quizchat message sent event
     *
     * @param \block_quizchat\event\message_sent $event
     */
    public static function message_sent(\block_quizchat\event\message_sent $event) {
        $messageid = $event->objectid;
        //\core\notification::info("A new Quizchat message with ID $messageid was sent.");
    }

    /**
     * Group updated event, change group name in block_quizchat_group table
     *
     * @param \core\event\group_updated $event
     */
    public static function moodle_group_updated(\core\event\group_updated $event) {
        global $DB;
        $g_name = $event->get_record_snapshot($event->objecttable, $event->objectid)->name;
        $g_id = $event->objectid;
        $updated = $DB->set_field('block_quizchat_messages', 'gname', $g_name, ['mgroupid' => $g_id]);
    }

    /**
     * Grouping updated event, change grouping name in block_quizchat_group table
     *
     * @param \core\event\group_updated $event
     */
    public static function moodle_grouping_updated(\core\event\grouping_updated $event) {
        global $DB;
        $g_name = $event->get_record_snapshot($event->objecttable, $event->objectid)->name;
        $g_id = $event->objectid;
        $updated = $DB->set_field('block_quizchat_messages', 'gname', $g_name, ['mgroupingid' => $g_id]);
    }

    /**
     * Group delete event, update group id in block_quizchat_group table
     *
     * @param \core\event\group_updated $event
     */
    public static function moodle_group_deleted(\core\event\group_deleted $event) {
        global $DB;
        $g_id = $event->objectid;
        $updated = $DB->set_field('block_quizchat_messages', 'mgroupid', 0, ['mgroupid' => $g_id]);
    }

    /**
     * Grouping delete event, update grouping id in block_quizchat_group table
     *
     * @param \core\event\group_updated $event
     */
    public static function moodle_grouping_deleted(\core\event\grouping_deleted $event) {
        global $DB;
        $g_id = $event->objectid;
        $updated = $DB->set_field('block_quizchat_messages', 'mgroupingid', 0, ['mgroupingid' => $g_id]);
    }
}