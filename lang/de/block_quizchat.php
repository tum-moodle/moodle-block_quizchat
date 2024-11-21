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
 * Strings for the block_quizchat plugin, language 'de' .
 *
 * @package   block_quizchat
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../lib/lib.php');

$string['pluginname'] = 'Quizchat';
$string['quizchat'] = 'Nachrichten vom Prüfer';
$string['quizchat:addinstance'] = 'Füge einen neuen Quizchat block hinzu';
$string['quizchat:sendall'] = 'Benachrichtigungen an alle Quiz-Teilnehmer:innen senden';
$string['quizchat:sendmsg'] = 'Eine neue Quizchat Nachricht an Dozent:in senden';
$string['block_chat_id'] = 'Chat Id';
$string['timeout'] = 'Polling in Millisekunden';
$string['blocktitle'] = 'Quizchat Überschrift';
$string['defaulttitle'] = 'Quizchat';
$string['title_message_to_teacher'] = 'Nachricht an Prüfer';
$string['notification_new_msg_singular'] = 'neue Nachricht';
$string['notification_new_msg_plural'] = 'neue Nachrichten';
$string['placeholder_student_send_input'] = 'Nachricht';
$string['placeholder_instructor_send_input'] = 'Nachricht';
$string['caption_student_send_submit'] = 'Senden';
$string['caption_instructor_send_submit'] = 'Senden';
$string['select_participant'] = 'Teilnehmer:innen';
$string['block_quizchat_users_select'] = 'Nachricht an:';
$string['everyone'] = 'Alle';
$string['instructors'] = 'Dozent:in';
$string['info_message_to_instructors_only'] = 'Nur Nachrichten an Dozent:in möglich.';
$string['missing_recipient'] = 'Bitte Empfänger:in auswählen!';
$string['from'] = 'Von';
$string['to'] = 'An';
$string['group'] = 'Gruppe';
$string['plugin_settings_unnotify_timeout_name'] = 'Unnotify Timeout [s]';
$string['plugin_settings_unnotify_timeout_desc'] = 'Zeitraum in Sekunden zwischen Empfang einer neuen Nachricht und deren Markierung als gelesen bei geöffnetem Drawer (ganze Zahl zwischen ' . QUIZCHAT_UNNOTIFY_TIMEOUT_MIN. ' and ' . QUIZCHAT_UNNOTIFY_TIMEOUT_MAX. ').';
$string['plugin_settings_poll_interval_name'] = 'Quizchat Poll Timeout [s]';
$string['plugin_settings_poll_interval_desc'] = 'Zeitraum in Sekunden zwischen Abfragen nach neuen Nachrichten beim Webservice (ganze Zahl zwischen ' . QUIZCHAT_POLL_TIMEOUT_MIN. ' und ' . QUIZCHAT_POLL_TIMEOUT_MAX. ').';
$string['unnotify_timeout_setting_too_short'] = 'Das Unnotify Timeout ist zu kurz. Verwende Minimum.';
$string['unnotify_timeout_setting_too_long'] = 'Das Unnotify Timeout ist zu lang. Verwende Maximum.';
$string['poll_timeout_setting_too_short'] = 'Das Poll Timeout ist zu kurz. Verwende Minimum.';
$string['poll_timeout_setting_too_long'] = 'Das Poll Timeout ist zu lang. Verwende Maximum.';
$string['plugin_settings_msg_length_name'] = 'Die Nachrichtlänge';
$string['plugin_settings_msg_length_desc'] = 'Die maximale Anzahl von Zeichen, die in einer einzigen Nachricht gesendet werden können.';
$string['plugin_settings_msg_length_too_short'] = 'Die Nachrichtlänge ist zu kurz. Verwende Minimum.';
$string['plugin_settings_msg_length_too_long'] = 'Die Nachrichtlänge ist zu lang. Verwende Maximum.';
$string['spantxt_charCount'] = ' Zeichen übrig.';
$string['titlesaved'] = 'Die Quizchat Einstellungen wurden erfolgreich gespeichert.';
$string['emptytitle'] = 'Da kein Titel angegeben wurde, wurde der Standardwert angewendet.';
$string['txtinput_required'] = 'Bitte füllen Sie dieses Feld aus.';
$string['unenrolled'] = 'Nicht eingeschrieben';
$string['deleted'] = 'Nicht eingeschrieben';
$string['abandoned'] = 'Versuch beendet';
$string['inprogress'] = 'Versuch begonnen';
$string['noattempt'] = 'Kein Versuch';
$string['finished'] = 'Versuch beendet';
$string['suspended'] = 'Nicht eingeschrieben';
$string['status'] = 'Status';
$string['privacy:metadata:block_quizchat'] = 'Daten der Quizchat-Blöcke. Verfolgt, welcher Chatblock in welchem Quiz und Kurs ist. Diese Daten sind temporär und werden gelöscht, nachdem die Chatsitzung gelöscht wurde.';
$string['privacy:metadata:block_quizchat:course'] = 'Course ID';
$string['privacy:metadata:block_quizchat:quiz'] = 'Quiz ID';
$string['privacy:metadata:block_quizchat:timecreated'] = 'Der Zeitpunkt, zu dem der Quizchat-Block zu einem Quiz hinzugefügt wurde.';
$string['privacy:metadata:block_quizchat_messages'] = 'Die während der Chat-Sitzungen gesendeten Nachrichten.';
$string['privacy:metadata:block_quizchat_messages:quizchatid'] = 'Quizchat ID';
$string['privacy:metadata:block_quizchat_messages:userid'     ] = 'Die Absender-ID der Quizchat-Nachricht';
$string['privacy:metadata:block_quizchat_messages:receiverid' ] = 'Die ID des Quizchat-Nachrichtenempfängers';
$string['privacy:metadata:block_quizchat_messages:groupid'    ] = 'Group ID';
$string['privacy:metadata:block_quizchat_messages:message'    ] = 'Die Quizchat-Nachricht';
$string['privacy:metadata:block_quizchat_messages:timestamp'  ] = 'Der Zeitpunkt, zu dem die Nachricht gesendet wurde';
$string['msgs'] = 'Nachrichten';
$string['alt_infoicon'] = 'Info';
$string['available_after_attempt'] = 'Quizchat ist erst ab dem Quiz-Versuch verfügbar.';
$string['student_question_select'] = 'Frage:';
$string['student_question_general'] = 'Allgemein';
$string['privacy:metadata:block_quizchat_messages:questionattemptid'] ='Auf welche Frage bezieht sich die Nachricht?';
$string['quiz_attempt_txt'] = 'Quizversuch:';
$string['group_txt'] = 'Gruppe';
$string['privacy:metadata:block_quizchat_messages:questionid'] = 'Auf welche Frage bezieht sich die Nachricht, wenn die Nachricht von Dozent:in geschickt würde?';
$string['today'] = 'Heute';
$string['send_msg_deactivated_student'] = 'Das Versenden von Nachrichten ist deaktiviert.';
$string['send_msg_deactivated_teacher'] = 'Die Quiz-Teilnehmer:innen Nachrichten sind deaktiviert.';
$string['help_deactivated_students_msgs'] = 'deaktivierte Studentennachrichten';
$string['help_deactivated_students_msgs_help'] = 'Studierende haben derzeit nicht die Berechtigung, Nachrichten im Quizchat zu senden. <br>Sie dürfen nur während eines Quizversuchs Nachrichten empfangen. <br>Um diese Berechtigung zu ändern, sollte die Fähigkeit "block/quizchat:sendmsg" aktualisiert werden auf der';
$string['role_permissions_page'] = 'Rollen-Rechte Seite';
$string['fullscreen'] = 'Quizchat in einer neuen Seite öffnen';
$string['attempt_deleted_notification'] = "Alle Quizchat-Nachrichten im Zusammenhang mit den gelöschten Quiz-Versuchen wurden erfolgreich gelöscht.";
$string['eventmessageadded'] = 'Eine neue Quizchat-Nachricht wurde gesendet.';
$string['sidemenu_you'] = 'Ich';

