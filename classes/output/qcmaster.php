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
namespace block_quizchat\output;
defined('MOODLE_INTERNAL') || die('dying');

use renderable;

use templatable;

require_once($CFG->dirroot . '/lib/grade/constants.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ .'/../../lib/lib.php');
/**
 * Class containing data for timeline block.
 *
 * @copyright  2018 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qcmaster implements renderable, templatable {

    /**
     * main constructor.
     *
     */
    // Avoid multiple initializations of js modules at frontend
    // Info: https://moodle.org/mod/forum/discuss.php?d=350835
    public static $amd_init_master_called = false;
    public static $amd_init_student_called = false;
    public static $amd_init_instructor_called = false;

    protected $_form;

    function get_title() {
        $this->title = get_string('defaulttitle', 'block_quizchat');
        return $this->title;
    }
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(\renderer_base $output)
    {
        global $USER, $DB, $PAGE, $OUTPUT;
        
        $data = new \stdClass();
        $quiz = $DB->get_record('quiz', array('id' => $PAGE->cm->instance));
        $quizid = $quiz->id;
        //get groups ids
        $groups = $DB->get_records('block_quizchat_group');
        // Check for browser security settings - there might be no drawer
        $seb_quizsettings = $DB->get_record('quizaccess_seb_quizsettings', array('quizid' => $quizid));
        $no_drawer_flag = (!preg_match('/\/mod\/quiz\/view\.php/', $PAGE->url)) && ((FALSE !== $seb_quizsettings) || ("-" !== $quiz->browsersecurity)) ? TRUE : FALSE;
        $fullscreen_flag = (preg_match('/\/blocks\/quizchat\/view\.php/', $PAGE->url)) ? TRUE : FALSE;
        
        $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quizid));
        
        //check sendall capability of the current user
        $data->is_teacher = check_sendallcap($quizchat);
        //check sendmsg capability of the current user
        $data->sendmsgcap = check_sendmsgcap($quizchat);
        $poll_timeout_setting = get_config('block_quizchat', 'quizchat_poll_timeout');
        $unnotify_timeout_setting = get_config('block_quizchat', 'unnotify_timeout');
        $data->quizchat_msg_length = get_config('block_quizchat', 'quizchat_msg_length');
        $data->mathjax_url = get_config('filter_mathjaxloader', 'httpsurl');
        $data->mathjax_config = get_config('filter_mathjaxloader', 'mathjaxconfig');
        $msg_len_setting = $data->quizchat_msg_length;
        //get language strings
        $txt_validation_msg = get_string('txtinput_required', 'block_quizchat');
        $lang_keys = [
            'notification_new_msg_singular',
            'notification_new_msg_plural',
            'everyone', 'instructors',
            'from', 'to', 'group',
            'unenrolled','deleted','abandoned',
            'inprogress','noattempt','finished','suspended','student_question_select', 'student_question_general','group_txt', 'quiz_attempt_txt','today'
        ];
        $langstr_obj = new \stdClass();
        $plugin_name = 'block_quizchat';
        // Loop through the language keys and get the corresponding strings
        foreach ($lang_keys as $key) {
            $langstr_obj->$key = get_string($key, $plugin_name);
        }
        // Check if the user has any attempts for the quiz
        $enableblock = check_blockavailability($quizchat->quiz);
        // Load and initialize all javascript modules
        // initializing constants via web service
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules#top_level_await
        if(!self::$amd_init_master_called){
            $msg_cookie_id = $USER->id . '_' . $quizid . '_' .time();
            $PAGE->requires->js_call_amd(
                'block_quizchat/master',
                'init', [
                    $quizchat,
                    intVal($USER->id),
                    $poll_timeout_setting,
                    $unnotify_timeout_setting,
                    $no_drawer_flag,$langstr_obj, $fullscreen_flag
            ]);
            self::$amd_init_master_called = true;
        }
        if(!$data->is_teacher && !self::$amd_init_student_called && self::$amd_init_master_called){
            $questions_form = new questions_form(null,["quizchatid" => $quizchat->id]);
            $data->enableblock = $enableblock;
            $data->questions_form = $questions_form->render();
            $PAGE->requires->js_call_amd(
                'block_quizchat/student',
                'init_student', [
                    $quizchat, $msg_len_setting, $txt_validation_msg, $groups
                ]);
            self::$amd_init_student_called = true;
        }
        if($data->is_teacher && !self::$amd_init_instructor_called && self::$amd_init_master_called){
            $data->quizchat_msg_length = get_config('block_quizchat', 'quizchat_msg_length');
            $data->sendmsgcap_students = check_sendmsgcap_students($quizchat);//check if students are allowed to send messages
            
            //// create help icon
            $helpicon = $OUTPUT->help_icon('help_deactivated_students_msgs', 'block_quizchat');
            // URL for permissions page
            $permissions_url = new \moodle_url('/admin/roles/permissions.php', array('contextid' => $quizchat->contextid));
            $permissionpage_txt = get_string('role_permissions_page', 'block_quizchat');
            // Create the content to append
            $appendContent = "<a href='{$permissions_url->out(false)}' id='rolepermission_link' name='rolepermission_link'> {$permissionpage_txt}</a>.";
            // Find position of '</p>' in the help icon
            $position = strpos($helpicon, '&lt;/p&gt;');
            // Insert content before the closing </p> tag
            $helpicon_mod = substr_replace($helpicon, htmlspecialchars($appendContent, ENT_QUOTES, 'UTF-8'), $position, 0);
            $data->helpicon = $helpicon_mod;

            $questions_form = new questions_form(null,["quizchatid" => $quizchat->id]);
            $instructor_form = new block_quizchat_instructor_form(null, ["id" => $quizchat->id]);
            $data->questions_form = $questions_form->render();
            $data->instructor_form = $instructor_form->render();
            $PAGE->requires->js_call_amd(
                'block_quizchat/instructor',
                'init_instructor', [
                    $quizchat, $msg_len_setting, $txt_validation_msg, $groups
                ]);
            self::$amd_init_instructor_called = true;
        }
        $PAGE->requires->css('/blocks/quizchat/quizchat.css');

        return $data;
    }
}




