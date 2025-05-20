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
 * JavaScript library for the block_quizchat plugin.
 *
 * @package
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as Notification from 'core/notification';
import {call as fetchMany} from 'core/ajax';
import $ from 'jquery';
import {
  quizchat_student_question_id,
  lang_strings
} from 'block_quizchat/master';

export const list = (userid,qcid,query) => {
    return fetchMany([{
        methodname: 'block_quizchat_get_questionslot',
        args: {
            senderid: userid,
            quizchatid: qcid,
            partial_name: query,
            general_string: lang_strings['student_question_general']
        }
    }])[0];
};

export const processResults = (selector, results) => {
    var options = [];
        let questions = results.questions;
        let option_value = 0;
        let nametag= '';
        questions.forEach(question => {
            let container = document.createElement("div");
            if(question.questionid == 0) {//if questionid = 0, general question case
                nametag='<div class="questions-menu" title="' + question.questionsummary + '">'
                + question.questionsummary + '</div>';
                container.innerHTML = nametag;
                container.title = question.questionsummary;
                option_value = question.questionattemptid;
            } else {//other questions
                //if the logged-in user has send-all cap(if teacher), innerHTML =  question.questionname
                //if student, innerHTML =  question.studentquestionorder
                if(question.questionid == quizchat_student_question_id) {//student
                    nametag='<div class="questions-menu" title="' + question.studentquestionorder + '">'
                    + question.studentquestionorder + '</div>';
                    container.innerHTML = nametag;
                    container.title = question.studentquestionorder;
                    option_value = question.questionattemptid;
                }
                else //teacher
                {
                    nametag='<div class="questions-menu" title="' + question.questionname + '">' + question.questionname + '</div>';
                    container.innerHTML = nametag;
                    container.title = question.questionname;
                    option_value = question.questionid;
                }
            }
            container.className = 'divcontainer-questions';
            options.push({
                value: option_value,
                label: container
            });
    });
    return options;
};

export const transport = (selector, query, callback) => {
    let userid = parseInt($('[name="block_quizchat_userid"]').val());
    let qcid = parseInt($('[name="block_quizchat_quizchatid"]').val());
    list(userid,qcid,query)
        .then(callback).catch(Notification.exception);
};
