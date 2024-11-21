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
import {call as fetchMany} from 'core/ajax';
import $ from 'jquery';
import {reset_poll_timeout as restart_polling} from 'block_quizchat/master';
import {
    checkCharsLength as checkMsgLength,
    resetCharsCount as updateCharsCount,
    handleWhiteSpaceMsg as checkWhiteSpaceMsg
} from 'block_quizchat/chars_limit';
import {
    quizchat_general_question_id,
    lang_strings,
    write_sessionStorage
} from 'block_quizchat/master';
let quizchatobj;
var msglen;
var requiredmsg;
var groups;
let htmlContent_questions;
let htmlContent_q_select;
let timeoutid;
var textinputcontrolname = '#block_quizchat_input_student_send';
const send_msg_to_instructor = (submit_event) => {
    submit_event.preventDefault();
    let whitespace_msg = checkWhiteSpaceMsg(textinputcontrolname);
    //if white space message
    if (whitespace_msg) {
        let msg_text_input = document.querySelector(textinputcontrolname);
        updateCharsCount(msglen);
        msg_text_input.setCustomValidity(requiredmsg);
        msg_text_input.reportValidity();
        return -1;
    }
    // Check if a question selection has been made
    if(0 === $('#fitem_id_block_quizchat_questions_select .form-autocomplete-selection [role="option"]').length){
        // Highlight select label and prevent form submit all together
        $('#fitem_id_block_quizchat_questions_select label').css({'color': '#f00', 'font-weight': '600'});
        return -1;
    }
    $('#questions_required').css('display', 'none');
    let questionattemptid = 0;//general
    if("" === $('#fitem_id_block_quizchat_questions_select option:selected')[0].value){
        // If a selection has been made and the search input gets focused and blurred again
        // without making a different selection the select element will have an empty
        // selected options property with no receiverid at all
        // => get selected option from span[role="option"]
        questionattemptid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
    } else {
        // The select element is ok, just get the value
        questionattemptid = $('#id_block_quizchat_questions_select').val();
    }
    //alert(questionattemptid);
    $('#id_block_quizchat_questions_select_label').css({'color': '#000', 'font-weight': 'normal'});
    $('#block_quizchat_input_student_send').prop("disabled", true);
    $('#block_quizchat_button_student_send').prop("disabled", true);
    updateCharsCount(msglen);
    let grps_ar = Object.keys(groups).map(key => {
        return {
            ...groups[key]
        };
    });
    let groupid = parseInt(grps_ar.find(obj => obj.name === 'teachers').id);//Send to teachers
    const calls = [
        {
            methodname: 'block_quizchat_create_message',
            args: {
                'quizchatid': quizchatobj.id,
                'receiverid': 0,
                'groupid': groupid,
                'message': $('#block_quizchat_input_student_send').val(),
                'questionattemptid': questionattemptid,
                'questionid' : 0
            }
        }
    ];
    fetchMany(calls)[0]
        .then((data) => {
            restart_polling();
            $('#block_quizchat_input_student_send').val('');
            resetMenu();
            // reset questions menu after sending message
            // $(
            //     '#block_quizchat_questions_form ul.form-autocomplete-suggestions li[role="option"][data-value="0"]'
            // ).trigger('click');
            $('#block_quizchat_input_student_send').prop("disabled", false);
            $('#block_quizchat_button_student_send').prop("disabled", false);
            return data.id;
        })
        .catch(() => {
            $('#block_quizchat_input_student_send').prop("disabled", false);
            $('#block_quizchat_button_student_send').prop("disabled", false);
        });
};

const text_oninput = (oninput_event) => {
    // Prevent default first
    oninput_event.preventDefault();
    oninput_event.target.setCustomValidity('');
    checkMsgLength(msglen, textinputcontrolname);
};

const text_onblur = (onblur_event) => {
    // Prevent default first
    onblur_event.preventDefault();
    //if white space message
    if (checkWhiteSpaceMsg(textinputcontrolname)) {
        updateCharsCount(msglen);
    }
    // reset input validity
    // see https://developer.mozilla.org/en-US/docs/Web/API/HTMLObjectElement/setCustomValidity
    onblur_event.target.setCustomValidity('');
};

const text_setvalidmsg = (e) => {
    // Customize the validation message
    e.target.setCustomValidity(requiredmsg);
};

const add_divcontainerquestions = () => {
    let contains_questions_div = $('#fitem_id_block_quizchat_questions_select span[role="option"]').find(
        '.divcontainer-questions').length > 0;
        if(!contains_questions_div &&
            $('#fitem_id_block_quizchat_questions_select span[role="option"]').attr('data-value')
            == quizchat_general_question_id) {
            $('#block_quizchat_questions_form span[role="option"]').each(function() {
                // replace the existing content with HTML content
                $(this).contents().last().replaceWith(htmlContent_questions);
            });
        }
};

const setMenu = () => {
    htmlContent_questions = '<div title="' + lang_strings['student_question_general']
            + '" class="divcontainer-questions"><div class="questions-menu" title="'
            + lang_strings['student_question_general'] + '">'
            + lang_strings['student_question_general'] + '</div></div>';
    htmlContent_q_select = $('#id_block_quizchat_questions_select').children().clone();
    add_divcontainerquestions();
    $('#block_quizchat_questions_form span[role="option"]').attr('data-value',quizchat_general_question_id);
    $('#block_quizchat_questions_form span[role="option"]').parent().attr('data-active-value',quizchat_general_question_id);
};

const setFormIfExist = () => {
    let q_form = document.querySelector('#block_quizchat_questions_form ul.form-autocomplete-suggestions');
    if (q_form) {
        setMenu();
        clearTimeout(timeoutid);
    } else {
        // The element is not yet rendered
        // Schedule another check after a delay
        timeoutid = setTimeout(setFormIfExist, 1000); // Check again after 1 second
    }
};

const resetMenu = () => {
    $('#block_quizchat_questions_form span[role="option"]').each(function() {
        // replace the existing content with HTML content
        $(this).find('.divcontainer-questions').replaceWith(htmlContent_questions);
    });
    $('#block_quizchat_questions_form span[role="option"]').attr('data-value', quizchat_general_question_id);
    $('#block_quizchat_questions_form span[role="option"]').parent().attr('data-active-value',quizchat_general_question_id);
    $('#id_block_quizchat_questions_select').empty();
    $('#id_block_quizchat_questions_select').append(htmlContent_q_select);
};

const ul_callback = (mutations) => {
    for (var mutation of mutations) {
        if (mutation.type === 'childList') {
            let attr = $('#block_quizchat_questions_form ul.form-autocomplete-suggestions').attr('aria-hidden');
            if (typeof attr == 'undefined' || attr == false) {
                 // Menu is visible, trigger deselection of the selected item
                 $('#block_quizchat_questions_form ul.form-autocomplete-suggestions li[aria-selected="true"]')
                 .attr('aria-selected', 'false');
            }
        }
    }
};

export const init_student = (arg_quizchat, confingsetting_msglen, reqmsg, receivergroups) => {
    import('block_quizchat/master').then(() => {
        quizchatobj = arg_quizchat;
        msglen = confingsetting_msglen;
        requiredmsg = reqmsg;
        groups = receivergroups;
        $('#block_quizchat_student_send').submit(send_msg_to_instructor);
        $('#id_block_quizchat_questions_select').change(() => {
            // Reset style of select label on correct selection
            $('#id_block_quizchat_questions_select_label').css({'color': '#000', 'font-weight': 'normal'});
        });
        $(textinputcontrolname).on('input', text_oninput);
        $(textinputcontrolname).on('blur', text_onblur);
        $(textinputcontrolname).on('invalid', text_setvalidmsg);
        setFormIfExist();
        write_sessionStorage('selected_user_or_question_id', "-2");
        let targetnode = $('#block_quizchat_questions_form');
        let ul_config = {childList: true, subtree: true};
        let ul_observer = new MutationObserver(ul_callback);
        ul_observer.observe(targetnode[0], ul_config);
    });
};
