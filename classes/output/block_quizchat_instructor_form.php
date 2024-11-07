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
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");

class block_quizchat_instructor_form extends \moodleform
{
    function definition()
    {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = $this -> _form;
        $infoicon = $OUTPUT->pix_icon('i/info', 'Info', 'core', array());
        $mform -> disable_form_change_checker();
        $mform->updateAttributes(array('class' => 'mform_quizchat', 'id' => 'block_quizchat_instructor_form'));
        $q_info = $DB -> get_record('quiz', array('id' => $PAGE -> cm -> instance));
        $options_ajax = array(
            'multiple' => false,
            'ajax' => 'block_quizchat/student_select',
            'class' => 'selectparticipant',
        );
        $autocomplete = $mform->createElement('autocomplete', 'block_quizchat_users_select', get_string('block_quizchat_users_select', 'block_quizchat'), [get_string('everyone', 'block_quizchat')], $options_ajax);
        $autocomplete->setValue(0);
        $autocomplete->updateAttributes(array('class'=> 'd-block w-100'));
        $mform->addElement($autocomplete);
        //$mform->setDefault('block_quizchat_users_select', 0);
        $msg_send_group = [
            $mform -> createElement('hidden', 'block_quizchat_quizid', $q_info -> id),
            $mform -> createElement('hidden', 'block_quizchat_langtxt_everyone', get_string('everyone', 'block_quizchat')),
            $mform -> createElement('hidden', 'block_quizchat_general', get_string('student_question_general', 'block_quizchat')),
            $mform -> createElement('hidden', 'block_quizchat_grouptxt', get_string('group_txt', 'block_quizchat')),
            $mform -> createElement('hidden', 'block_quizchat_enableflag', 1),//block_quizchat_enableflag is 1 to allow changing participants menu if questions menu is changed. It is 0 in case click_to_respond.
            $mform->createElement(
                'html', '
                <div class="input-group mb-3">
                    <input type="text" required name="block_quizchat_input_instructor_send" id="block_quizchat_input_instructor_send" class="form-control ml-0 mr-0" placeholder="'
                . get_string('placeholder_instructor_send_input', 'block_quizchat')
                . '" aria-label="'
                . get_string('placeholder_instructor_send_input', 'block_quizchat')
                . '" title="'
                . get_string('txtinput_required', 'block_quizchat')
                . '" aria-describedby="block_quizchat_button_instructor_send">
                            <div class="input-group-append">
                                <button name="block_quizchat_button_instructor_send" class="btn btn-primary mt-0 mr-0 mb-0" type="submit" id="block_quizchat_button_instructor_send">'
                . get_string('caption_instructor_send_submit', 'block_quizchat')
                . '</button>
                            </div>      
                </div><br>'
            )
        ];
        $mform->setType('block_quizchat_quizid', PARAM_INT);
        $mform->setType('block_quizchat_input_instructor_send', PARAM_RAW);
        $mform->setType('block_quizchat_langtxt_everyone', PARAM_RAW);
        $mform->setType('block_quizchat_general', PARAM_RAW);
        $mform->setType('block_quizchat_grouptxt', PARAM_RAW);
        $mform->setType('block_quizchat_enableflag', PARAM_INT);
        $mform->addGroup($msg_send_group, 'block_quizchat_msg_send_group', '', '', false);
    }

}
