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

class questions_form extends \moodleform
{
    function definition()
    {
        global $CFG, $DB, $PAGE, $OUTPUT, $USER;
        $mform = $this -> _form;
        $mform -> disable_form_change_checker();
        $mform->updateAttributes(array('class' => 'mform_quizchat', 'id' => 'block_quizchat_questions_form'));
        $options_ajax = array(
            'multiple' => false,
            'ajax' => 'block_quizchat/question_select',
            'class' => 'selectquestion',
        );
        $autocomplete = $mform->createElement('autocomplete', 'block_quizchat_questions_select', get_string('student_question_select', 'block_quizchat'), [get_string('student_question_general', 'block_quizchat')], $options_ajax);
        $autocomplete->setValue(0);
        $autocomplete->updateAttributes(array('class'=> 'd-block w-100'));
        $mform->addElement($autocomplete);
        $msg_send_group = [
            $mform -> createElement('hidden', 'block_quizchat_userid', $USER->id),
            $mform -> createElement('hidden', 'block_quizchat_quizchatid', $this->_customdata['quizchatid'])
        ];
        $mform->setType('block_quizchat_userid', PARAM_INT);
        $mform->setType('block_quizchat_quizchatid', PARAM_INT);
        $mform->addGroup($msg_send_group, 'block_quizchat_msg_send_group', '', '', false);
    } 

}
