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
 * Strings for the block_quizchat plugin, language 'en' .
 *
 * @package   block_quizchat
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../lib/lib.php');

$string['pluginname'] = 'Quizchat';
$string['quizchat'] = 'Messages from Teacher';
$string['quizchat:addinstance'] = 'Add a new instance of Quizchat block';
$string['quizchat:sendall'] = 'Send Quizchat messages to all quiz participants';
$string['quizchat:sendmsg'] = 'Send a new Quizchat message to instructors';
$string['block_chat_id'] = 'Chat Id';
$string['timeout'] = 'Polling in Milliseconds';
$string['blocktitle'] = 'Quizchat Title';
$string['defaulttitle'] = 'Quizchat';
$string['title_message_to_teacher'] = 'Message to Teacher';
$string['notification_new_msg_singular'] = 'new message';
$string['notification_new_msg_plural'] = 'new messages';
$string['placeholder_student_send_input'] = 'Message';
$string['placeholder_instructor_send_input'] = 'Message';
$string['caption_student_send_submit'] = 'Send';
$string['caption_instructor_send_submit'] = 'Send';
$string['select_participant'] = 'Participants';
$string['block_quizchat_users_select'] = 'Send message to:';
$string['everyone'] = 'Everyone';
$string['instructors'] = 'Instructors';
$string['info_message_to_instructors_only'] = 'You can only chat with the course instructor.';
$string['missing_recipient'] = 'Please select recipient!';
$string['from'] = 'From';
$string['to'] = 'To';
$string['group'] = 'Group';
$string['plugin_settings_unnotify_timeout_name'] = 'Unnotify Timeout [s]';
$string['plugin_settings_unnotify_timeout_desc'] = 'Timeout in seconds between receiving a message and marking all new messages as read when the block drawer is open (integer between ' . QUIZCHAT_UNNOTIFY_TIMEOUT_MIN. ' and ' . QUIZCHAT_UNNOTIFY_TIMEOUT_MAX. ').';
$string['plugin_settings_poll_interval_name'] = 'Quizchat Poll Timeout [s]';
$string['plugin_settings_poll_interval_desc'] = 'Timeout in seconds for polling the webservice for new messages (integer between ' . QUIZCHAT_POLL_TIMEOUT_MIN. ' and ' . QUIZCHAT_POLL_TIMEOUT_MAX. ').';
$string['unnotify_timeout_setting_too_short'] = 'Unnotify timeout too short. Minimum set.';
$string['unnotify_timeout_setting_too_long'] = 'Unnotify timeout too long. Maximum set.';
$string['poll_timeout_setting_too_short'] = 'The poll timeout is too short. Minimum set.';
$string['poll_timeout_setting_too_long'] = 'The poll timeout is too long. Maximum set.';
$string['plugin_settings_msg_length_name'] = 'Message length';
$string['plugin_settings_msg_length_desc'] = 'The maximum number of characters to be sent in a single message (integer between ' . QUIZCHAT_MSG_LENGTH_MIN. ' and ' . QUIZCHAT_MSG_LENGTH_MAX. ').';
$string['plugin_settings_msg_length_too_short'] = 'The Message length is too short. Minimum set.';
$string['plugin_settings_msg_length_too_long'] = 'The Message length is too long. Maximum set.';
$string['spantxt_charCount'] = ' character(s) remaining.';
$string['titlesaved'] = 'Quizchat settings have been saved successfully.';
$string['emptytitle'] = 'Since the provided title was empty, the default value has been applied.';
$string['txtinput_required'] = 'Please fill out this field.';
$string['unenrolled'] = 'Unenrolled';
$string['deleted'] = 'Unenrolled';
$string['abandoned'] = 'Attempt finished';
$string['inprogress'] = 'Attempt started';
$string['noattempt'] = 'No attempt';
$string['finished'] = 'Attempt finished';
$string['suspended'] = 'Unenrolled';
$string['status'] = 'Status';
$string['privacy:metadata:block_quizchat'] = 'Quizchat blocks data. Keeps track of which chat block is in which quiz and course. This data is temporary and is deleted after the chat session is deleted.';
$string['privacy:metadata:block_quizchat:course'] = 'Course ID';
$string['privacy:metadata:block_quizchat:quiz'] = 'Quiz ID';
$string['privacy:metadata:block_quizchat:timecreated'] = 'The time when the Quizchat block was added to a quiz.';
$string['privacy:metadata:block_quizchat_messages'] = 'The messages sent during chat sessions.';
$string['privacy:metadata:block_quizchat_messages:quizchatid'] = 'Quizchat ID';
$string['privacy:metadata:block_quizchat_messages:userid'     ] = 'The Quizchat message sender ID';
$string['privacy:metadata:block_quizchat_messages:receiverid' ] = 'The message reciever ID';
$string['privacy:metadata:block_quizchat_messages:groupid'    ] = 'Group ID';
$string['privacy:metadata:block_quizchat_messages:message'    ] = 'The Quizchat message';
$string['privacy:metadata:block_quizchat_messages:timestamp'  ] = 'The time when the message was sent';
$string['msgs'] = 'Messages';
$string['alt_infoicon'] = 'Info';
$string['available_after_attempt'] = 'Quizchat will not be available until quiz attempt starts.';
$string['student_question_select'] = 'Question:';
$string['student_question_general'] = 'General';
$string['privacy:metadata:block_quizchat_messages:questionattemptid'] = 'Which question was referenced in the message.';
$string['quiz_attempt_txt'] = 'Quiz-attempt:';
$string['group_txt'] = 'Group';
$string['privacy:metadata:block_quizchat_messages:questionid'] = 'What question does the message refer to if the message were sent by the lecturer?';
$string['today'] = 'Today';
$string['send_msg_deactivated_student'] = 'Sending messages is deactivated.';
$string['send_msg_deactivated_teacher'] = 'Students messages are deactivated.';
$string['help_deactivated_students_msgs'] = 'deactivated students messages';
$string['help_deactivated_students_msgs_help'] = 'Students currently do not have permission to send messages in Quizchat. <br>They are only allowed to receive messages during a quiz attempt. <br>To change this permission, the capability "block/quizchat:sendmsg" should be updated in';
$string['role_permissions_page'] = 'role permissions page';
$string['fullscreen'] = 'Open Quizchat in a new page';
$string['attempt_deleted_notification'] = 'All Quizchat-messages related to the deleted quiz attempt(s) have been successfully deleted.';