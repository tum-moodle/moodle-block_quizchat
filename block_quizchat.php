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
use core_completion\progress;
require_once(__DIR__ .'/lib/lib.php');

class block_quizchat extends block_base
{

    public $quizchatid;
    public $timeout_val;
    public $title_message_to_teacher;

    public static $quizchat_instance_added = false;

    public function init()
    {
        $this->title = get_string('defaulttitle', 'block_quizchat');
        $this->title_message_to_teacher = get_string('title_message_to_teacher', 'block_quizchat');
    }

    public function get_content()
    {
        global $DB,$PAGE, $USER;
        $cm = $PAGE->cm;
        // from /mod/quiz/lib.php:
        if(!$quiz_info = $DB->get_record('quiz', ['id' => $cm->instance], '*')){
            die('Failed to get quiz info!');
        }
        $course = $PAGE->course;
        $this->page->requires->css('/blocks/quizchat/quizchat.css');
        //get block_quizchat-title config
        if(is_null($this->config))
        {
            $this->title = get_string('defaulttitle', 'block_quizchat');
        }
        else
        {
            $this->title = $this->config->title;
        }

        $quizchat = $DB->get_record('block_quizchat', array('quiz' => $quiz_info->id));
        if(!$quizchat){
            $data = new stdClass();
            $data->name = $this->title;
            $data->course = $course->id;
            $data->quiz = $quiz_info->id;
            $data->id = 1;
            $data->cmid = $cm->id;
            $data->parentcontextid = $PAGE->context->id;
            $data->contextid = $this->context->id;
            $data->instanceid = $this->instance->id;
            quizchat_add_instance($data);
        }

        $renderable = new \block_quizchat\output\qcmaster();
        $renderer = $this->page->get_renderer('block_quizchat');
        $this->content = new \stdClass();
        $this->content->text = $renderer->render($renderable);
        return $this->content;
    }

    public function get_content_for_output($output) {
        global $PAGE, $DB;
        $content = parent::get_content_for_output($output);
        $blockinstanceid = $this->instance->id;
        $quizchat = $DB->get_record('block_quizchat', array('instanceid' => $blockinstanceid));
        if (check_sendallcap($quizchat)) {
            $controls = $content->controls;
            // Add custom action to the menu
            $url = new moodle_url('/blocks/quizchat/view.php', array('blockid' => $this->instance->id, 'cmid' => $PAGE->cm->id));
            $icon = new pix_icon('e/layers', get_string('fullscreen', 'block_quizchat'));
            $action = new action_menu_link_primary($url, $icon, get_string('fullscreen', 'block_quizchat'), array('class' => 'fullscreen_actionmenu_item'));
            array_unshift($controls,$action);
            $content->controls = $controls;
        }

        return $content;
    }

    function instance_delete() {
        global $DB;
        $quizchat = $DB->get_record('block_quizchat', ['contextid' => $this->context->id ?? 'None'], '*');
        $quizchat_messages = $DB->get_records('block_quizchat_messages', array('quizchatid'=> $quizchat->id ?? 'None'));
        if(!empty($quizchat_messages)){
            $DB->delete_records('block_quizchat_messages', array('quizchatid'=> $quizchat->id));
        }
        if(!empty($quizchat)){
            $DB->delete_records('block_quizchat', array('id'=> $quizchat->id ?? 'None'));
        }
    }

    public function instance_allow_multiple()
    {
        return false;
    }

    public function can_block_be_added($page): bool {
        // check if the block already exists for the current quiz
        global $DB;
        // Get the current quiz context
        $context = $page->context;

        // Check if the context is a quiz module
        if ($context->contextlevel == CONTEXT_MODULE) {
            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if ($cm && $cm->course != 1) {
                // Check if an instance of quizchat block already exists for the current quiz
                $block_instance = $DB->get_record('block_instances', array('blockname' => 'quizchat', 'parentcontextid' => $context->id));

                // If an instance already exists, return false to prevent adding another instance
                if ($block_instance) {
                    return false;
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    function has_config()
    {
        return true;
    }

    function applicable_formats()
    {
        return array('mod-quiz' => true);
    }

    public function hide_header()
    {
        return false;
    }

    public function html_attributes()
    {
        $attributes = parent::html_attributes(); // Get default values
        $attributes['class'] .= ' block_' . $this->name(); // Append our class to class attribute
        return $attributes;
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        // Remove &nbsp; and &#160; occurrences and replace them with a space, then trim leading and trailing spaces
        $data->title = trim(str_replace(['&nbsp;', '&#160;'], ' ', $data->title));
        // Save the updated title to the block instance's configuration data if not empty. If empty set it to default title
        if($data->title!= '')
        {
            $this->title = $data;
            \core\notification::info(get_string('titlesaved', 'block_quizchat'));
        }
        else
        {
            $this->title = get_string('defaulttitle', 'block_quizchat');
            $data->title = get_string('defaulttitle', 'block_quizchat');
            \core\notification::info(get_string('emptytitle', 'block_quizchat'));
        }
        parent::instance_config_save($data, $nolongerused);
    }
}
