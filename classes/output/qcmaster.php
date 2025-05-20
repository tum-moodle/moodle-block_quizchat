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
        global $USER, $DB, $PAGE, $OUTPUT, $CFG;
        require_once($CFG->libdir .'/grouplib.php');
        require_once($CFG->dirroot . '/group/lib.php');
        $data = new \stdClass();
        $quiz = $DB->get_record('quiz', array('id' => $PAGE->cm->instance));
        $quizid = $quiz->id;
        //get groups ids
        $groups = $DB->get_records('block_quizchat_group');
        // Check for browser security settings - there might be no drawer
        $seb_quizsettings = $DB->get_record('quizaccess_seb_quizsettings', array('quizid' => $quizid));
        $no_drawer_flag = (!preg_match('/\/mod\/quiz\/view\.php/', $PAGE->url)) && ((FALSE !== $seb_quizsettings) || ("-" !== $quiz->browsersecurity)) ? TRUE : FALSE;
        $fullscreen_flag = (preg_match('/\/blocks\/quizchat\/view\.php/', $PAGE->url)) ? TRUE : FALSE;
        // Enable fullscreen mode if the url contains the fullscreen-url
        $data->fullscreen = $fullscreen_flag;
        
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
            'inprogress','noattempt','finished','suspended','student_question_select', 'student_question_general','group_txt', 'quiz_attempt_txt','today', 'sidemenu_you','strftimerecentfull', 'deleted_langstr'
        ];
        $langstr_obj = new \stdClass();
        $plugin_name = 'block_quizchat';
        // Loop through the language keys and get the corresponding strings
        foreach ($lang_keys as $key) {
            if(strcmp($key, 'strftimerecentfull')  == 0) {
                $langstr_obj->$key = get_string($key, 'core_langconfig');
            }
            else
            {
                $langstr_obj->$key = get_string($key, $plugin_name);
            }
        }
        $data->langstr_json = json_encode($langstr_obj);
        // Check if the user has any attempts for the quiz
        $enableblock = check_blockavailability($quizchat->quiz);
        $group_access = false;
        $quizobj = \mod_quiz\quiz_settings::create_for_cmid($quizchat->cmid);
        $cm = $quizobj->get_cm();
        if($data->is_teacher && groups_get_activity_groupmode($cm)) {
            if(is_quiz_accessible_to_group((int)$quizchat->quiz)) {
                $group_access = true;
            }
        }
        else if($data->is_teacher && !groups_get_activity_groupmode($cm)){
            $group_access = true;
        }
        else if(!$data->is_teacher) {
            $group_access = true;
        }
        // Load and initialize all javascript modules
        // initializing constants via web service
        // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Modules#top_level_await
        if((!self::$amd_init_master_called && $group_access && $data->is_teacher) || (!self::$amd_init_master_called && $enableblock && !$data->is_teacher)){
            $msg_cookie_id = $USER->id . '_' . $quizid . '_' .time();
            $PAGE->requires->js_call_amd(
                'block_quizchat/master',
                'init', [
                    $quizchat,
                    intVal($USER->id),
                    $poll_timeout_setting,
                    $unnotify_timeout_setting,
                    $no_drawer_flag, $fullscreen_flag
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
        if(($data->is_teacher && !self::$amd_init_instructor_called && self::$amd_init_master_called) ||  ($data->is_teacher && !self::$amd_init_instructor_called && !$group_access)){
            $data->quizchat_msg_length = get_config('block_quizchat', 'quizchat_msg_length');
            $data->sendmsgcap_students = check_sendmsgcap_students($quizchat);//check if students are allowed to send messages
            
            //// create help icon
            // URL for permissions page
            $permissions_url = new \moodle_url('/admin/roles/permissions.php', array('contextid' => $quizchat->contextid));
            $quizchat->permissions_url = $permissions_url->out(false);
            $data->permissions_url = $quizchat->permissions_url;

            $questions_form = new questions_form(null,["quizchatid" => $quizchat->id]);
            $instructor_form = new block_quizchat_instructor_form(null, ["id" => $quizchat->id]);

            //get groups menu
            $url = $PAGE->url;
            $groups_menu = '';
            $group_access_notify = '';
            // A new groupselector is unnecessary if the block is added to the results page, as the page already contains one.
            $results_page = (strpos($url->out(false), "mod/quiz/report.php")?true:false);
            if (groups_get_activity_groupmode($cm) && !$results_page) {
                // Groups are being used, so output the group selector if we are not downloading.
                $groups_menu = groups_print_activity_menu($cm, $url, true);
                if(!$group_access) {
                    $group_access_notify = '<div class="alert alert-danger fade in" role="alert">'.
                    get_string('quiz_not_accessible', 'block_quizchat').
                    '</div>';
                }
                // Add line
                if(!$fullscreen_flag){
                    $groups_menu .= $group_access_notify.'<div class="line-with-text" id="empty_line"><hr></div>';
                }
                else {
                    $groups_menu = 
                    '<div class="panel-header-container">
                        <div class="border-bottom p-1 px-sm-2">'.
                        $groups_menu.$group_access_notify.
                        '</div>
                    </div>';
                }
                
            }
            else if(groups_get_activity_groupmode($cm) && $results_page) {
                if(!$fullscreen_flag && !$group_access){
                    $group_access_notify = '<div class="alert alert-danger fade in" role="alert">'.
                    get_string('quiz_not_accessible', 'block_quizchat').
                    '</div>';
                    $groups_menu .= $group_access_notify.'<div class="line-with-text" id="empty_line"><hr></div>';
                }
            }
            $data->groups_menu = $groups_menu;
            $data->group_access = $group_access;
            $data->questions_form = $group_access? $questions_form->render() : '';
            $data->instructor_form = $group_access? $instructor_form->render() : '';
            if($group_access) {
                $PAGE->requires->js_call_amd(
                    'block_quizchat/instructor',
                    'init_instructor', [
                        $quizchat, $msg_len_setting, $txt_validation_msg, $groups
                    ]);
                self::$amd_init_instructor_called = true;
            }
        }
        $PAGE->requires->css('/blocks/quizchat/quizchat.css');

        return $data;
    }
}




