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
        quizchat_address_everyone,
        quizchat_userid,
        quizchat_address_question_group,
        quizchat_general_question_id,
        quizchat_users,
        lang_strings,
        quizchat_msg,
        quizchat_msg_counters,
        update_msg_area,
        update_current_msgs_array,
        allmsgs_id,
        allmsgs_count_notf,
        quizchatid,
        isToday,
        write_sessionStorage
    } from 'block_quizchat/master';
import {
    checkCharsLength as checkMsgLength,
    resetCharsCount as updateCharsCount,
    handleWhiteSpaceMsg as checkWhiteSpaceMsg
} from 'block_quizchat/chars_limit';
import { full_screen_flag, int_sessionStorage } from './master';

let quizchatobj;
var msglen;
var requiredmsg;
var textinputcontrolname = '#block_quizchat_input_instructor_send';
var groupid = 0;//in case one to one message
var groups;
let timeoutid;
let timeoutid_private_group_btns;
let htmlContent_participants;
let htmlContent_questions;
let htmlContent_q_select;
let htmlContent_p_select;
let noparticipant_flag = false;
let li_clicked = false;
let grp_img_white = M.util.image_url('g/g1', 'core');
let grp_img_white_grey = M.cfg.wwwroot+ '/blocks/quizchat/img/g1_white_grey.png';
export var is_teacher = false;

const send_msg = (submit_event) => {
    // Prevent default first
    submit_event.preventDefault();
    let receiverid, questionid, grps_ar;
    //if white space message
    if (checkWhiteSpaceMsg(textinputcontrolname))
    {
        let msg_text_input = document.querySelector(textinputcontrolname);
        updateCharsCount(msglen);
        msg_text_input.setCustomValidity(requiredmsg);
        msg_text_input.reportValidity();
        return -1;
    }
    if(!full_screen_flag ||
        (full_screen_flag &&
            (!$('#fitem_id_block_quizchat_questions_select').is(':hidden')
            && !$('#fitem_id_block_quizchat_users_select').is(':hidden'))
        )
    ) {
        // Check if a recipient selection has been made
        if(0 === $('#fitem_id_block_quizchat_users_select .form-autocomplete-selection [role="option"]').length){
            // Highlight select label and prevent form submit all together
            $('#fitem_id_block_quizchat_users_select label').css({'color': '#f00', 'font-weight': '600'});
            // Check if a question selection has been made
            if(0 === $('#fitem_id_block_quizchat_questions_select .form-autocomplete-selection [role="option"]').length){
                // Highlight select label and prevent form submit all together
                $('#fitem_id_block_quizchat_questions_select label').css({'color': '#f00', 'font-weight': '600'});
            }
            else {
                $('#fitem_id_block_quizchat_questions_select label').css({'color': '#000', 'font-weight': 'normal'});
            }
            return -1;
        }
        receiverid = $('#fitem_id_block_quizchat_users_select span[role="option"]')[0].dataset.value;
        if(parseInt(receiverid) == quizchat_address_question_group) {
            //in case question group receiver id- make receiverid 0 and save question id
            receiverid = 0;
        }
        // Check if a question selection has been made
        if(0 === $('#fitem_id_block_quizchat_questions_select .form-autocomplete-selection [role="option"]').length){
            // Highlight select label and prevent form submit all together
            $('#fitem_id_block_quizchat_questions_select label').css({'color': '#f00', 'font-weight': '600'});
            return -1;
        }
        $('#questions_required').css('display', 'none');
        //let general_string = lang_strings['student_question_general'];
        questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
        grps_ar=Object.keys(groups).map(key => {
            return {
              ...groups[key]
            };
          });
        if (parseInt(receiverid) == quizchat_address_everyone)
        {
            if(parseInt($('#fitem_id_block_quizchat_users_select span[role="option"]')[0].dataset.value)
            == quizchat_address_question_group && parseInt(questionid) != quizchat_general_question_id) {
                groupid = 0;//In case one to one message or group question message
            }
            else {
                groupid = parseInt(grps_ar.find(obj => obj.name === 'all').id);//send to all
            }
        }
        else
        {
            groupid = 0;//In case one to one message or group question message
        }
        $('#id_block_quizchat_users_select_label').css({'color': '#000', 'font-weight': 'normal'});
        $('#fitem_id_block_quizchat_users_select input[type="text"]').css({'color': '#000', 'font-weight': 'normal'});
        $('#block_quizchat_input_instructor_send').prop("disabled", true);
        $('#block_quizchat_button_instructor_send').prop("disabled", true);
    } else {
        //case question group conversation or everyone conversation
        if(full_screen_flag &&
            ($('#fitem_id_block_quizchat_questions_select').is(':hidden')
            && $('#fitem_id_block_quizchat_users_select').is(':hidden'))
        ) {
            receiverid = 0;
            questionid = $('#conv-header').data('group-id');
            let grps_ar=Object.keys(groups).map(key => {
                return {
                  ...groups[key]
                };
              });
            if (parseInt(questionid) == quizchat_address_everyone)
            {
                groupid = parseInt(grps_ar.find(obj => obj.name === 'all').id);//send to all
                questionid = 0;
            }
            else
            {
                questionid = $('#conv-header').data('group-id');//In case question group conversation
                groupid = 0;
            }
            $('#id_block_quizchat_users_select_label').css({'color': '#000', 'font-weight': 'normal'});
            $('#fitem_id_block_quizchat_users_select input[type="text"]').css({'color': '#000', 'font-weight': 'normal'});
            $('#block_quizchat_input_instructor_send').prop("disabled", true);
            $('#block_quizchat_button_instructor_send').prop("disabled", true);
        } else if(full_screen_flag &&
            (!$('#fitem_id_block_quizchat_questions_select').is(':hidden'))
        ) {//case private conversation
            // Check if a question selection has been made
            if(0 === $('#fitem_id_block_quizchat_questions_select .form-autocomplete-selection [role="option"]').length){
                // Highlight select label and prevent form submit all together
                $('#fitem_id_block_quizchat_questions_select label').css({'color': '#f00', 'font-weight': '600'});
                return -1;
            }
            else {
                $('#fitem_id_block_quizchat_questions_select label').css({'color': '#000', 'font-weight': 'normal'});
            }
            $('#questions_required').css('display', 'none');
            receiverid = $('#conv-header').data('user-id');
            questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
            groupid = 0;
            $('#id_block_quizchat_users_select_label').css({'color': '#000', 'font-weight': 'normal'});
            $('#fitem_id_block_quizchat_users_select input[type="text"]').css({'color': '#000', 'font-weight': 'normal'});
            $('#block_quizchat_input_instructor_send').prop("disabled", true);
            $('#block_quizchat_button_instructor_send').prop("disabled", true);
        }
    }
    updateCharsCount(msglen);
    const calls = [
        {
            methodname: 'block_quizchat_create_message',
            args: {
                'quizchatid': quizchatobj.id,
                'receiverid': parseInt(receiverid),
                'groupid': groupid,
                'message': $('#block_quizchat_input_instructor_send').val(),
                'questionattemptid': 0,
                'questionid' : parseInt(questionid)
            }
        }
    ];
    fetchMany(calls)[0]
    .then( (data) => {
        restart_polling();
        $('#block_quizchat_input_instructor_send').val('');
        resetMenus();
        $('#block_quizchat_input_instructor_send').prop("disabled", false);
        $('#block_quizchat_button_instructor_send').prop("disabled", false);
        $('#block_quizchat_input_instructor_send').trigger('focus');
        return data.id;
    })
    .catch(() => {
        $('#block_quizchat_input_instructor_send').prop("disabled", false);
        $('#block_quizchat_button_instructor_send').prop("disabled", false);
    });
};

const text_oninput = (oninput_event) => {
    // Prevent default first
    oninput_event.preventDefault();
    checkMsgLength(msglen , textinputcontrolname);
    oninput_event.target.setCustomValidity('');
};

const text_onblur = (onblur_event) => {
    // Prevent default first
    onblur_event.preventDefault();
    //if white space message
    if (checkWhiteSpaceMsg(textinputcontrolname))
    {
        updateCharsCount(msglen);
    }
    onblur_event.target.setCustomValidity('');
};

const get_user_state = (userid) => {
    let user = quizchat_users.find(u => u.id === userid);
    return user.state;
};

export const autofill_users_select = (event) => {
    event.preventDefault();
    // hrefs be like '#userid/questionid/questiontxt'
    const [userid, questionid, questiontxt] = event.target.getAttribute('href').slice(1).split('/');
    //check whether menus are visible
    if(!$('#fitem_id_block_quizchat_users_select').is(':hidden') && !$('#fitem_id_block_quizchat_questions_select').is(':hidden')) {
        $('#id_block_quizchat_enableflag').attr('value', 0);
        resetMenus_click_respond(userid, questionid, questiontxt);
        update_user_select_status_indicators();
        event.target.blur();
    } else {
        //check whether a conversation already exists, then click it
        let traget_conv_id = 'side-menu-conv-link-user-' + userid;
        let target_conv_el = $('#'+traget_conv_id);
        if(typeof target_conv_el.html() != 'undefined') {
            target_conv_el.trigger('click');
            resetMenus();
            resetMenus_click_respond(userid, questionid, questiontxt);
            update_user_select_status_indicators();
            event.target.blur();
        } else {
            //if there is no conversation, create a new one
            write_sessionStorage('selected_user_or_question_id', userid);
            write_sessionStorage('grp_flag', false);
            update_conversation_header(userid,false);
            $('#block_quizchat_messages > .block_quizchat_msg_area_body').empty();
            resetMenus();
            resetMenus_click_respond(userid, questionid, questiontxt);
            toggle_autocomplete(true, $('#fitem_id_block_quizchat_questions_select'));
            toggle_autocomplete(false, $('#fitem_id_block_quizchat_users_select'));
            write_sessionStorage('total_unread_msg_usr_qs', 0);
            let filteredMessages = [];
            update_current_msgs_array(filteredMessages);
            let key = 'newmsgscount_userid_'+ userid;
            write_sessionStorage('latest_msg_id_usr_qs', 0);
            write_sessionStorage(key, '0');
            //newmsgs_count_notf(userid,false);
            update_user_select_status_indicators();
            event.target.blur();
        }
    }
};

export const update_user_select_status_indicators = () => {
    $('#block_quizchat_instructor_form ul.form-autocomplete-suggestions li[role="option"]').each(function(i, el) {
        let userid = parseInt(el.getAttribute('data-value'));
        if(isNaN(userid)) {
            // ignore empty first entry of list
            return;
        }
        let userstate = get_user_state(userid);
        let status_indicator_el = $('div.statecircle-base-menu', $(el));
        status_indicator_el.removeClass();
        status_indicator_el.addClass('statecircle-base-menu ' + userstate);
        status_indicator_el.attr('title', lang_strings[userstate]);
    });
    let chosen_option_span = $('#block_quizchat_instructor_form span[role="option"]');
    if(0 >= chosen_option_span.length) {
        // First time there may be no such element
        return;
    }
    let chosen_userid = parseInt(chosen_option_span.attr('data-value'));
    let chosen_user_state = get_user_state(chosen_userid);
    let chosen_status_indicator = $('div.statecircle-base-menu', chosen_option_span);
    chosen_status_indicator.removeClass();
    chosen_status_indicator.addClass('statecircle-base-menu ' + chosen_user_state);
    chosen_status_indicator.attr('title', lang_strings[chosen_user_state]);
};

const text_setvalidmsg = (e) => {
    // Customize the validation message
    e.target.setCustomValidity(requiredmsg);
};

const users_select_change = () => {
    let enableflag= $('#id_block_quizchat_enableflag').attr('value');
    if(enableflag == 0)
    {
        $('#id_block_quizchat_enableflag').attr('value', 1);
    }
};

const questions_select_change = () => {//onchange_event
    let enableflag= $('#id_block_quizchat_enableflag').attr('value');
    if((!$('#fitem_id_block_quizchat_users_select').is(':hidden') && full_screen_flag) || !full_screen_flag) {
    if(enableflag == 1 )
    {
    // Reset style of select label on correct selection
    $('#id_block_quizchat_questions_select_label').css({'color': '#000', 'font-weight': 'normal'});
    let group_string = lang_strings['group_txt'];
    //let everyone_string = lang_strings['everyone'];
    let questionid;
    let participantid;
    // => get selected option from span[role="option"]
    let selected_question = document.querySelector('#fitem_id_block_quizchat_questions_select span[role="option"]');
    let selected_participant = document.querySelector('#fitem_id_block_quizchat_users_select span[role="option"]');
    if(selected_question)
    {
        questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
        if (selected_participant) {
            participantid = $('#fitem_id_block_quizchat_users_select span[role="option"]')[0].dataset.value;
            if(questionid == String(quizchat_general_question_id)) {//general question 0
                if(participantid != String(quizchat_address_everyone)) {
                    //remove the selected participant
                    $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                    $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                    //select everyone in participants
                    observeForElement('#block_quizchat_instructor_form li');
                }
            }
            else {//question selected (not general) and there is selected participant
                //select question group in participants
                let gname = $('#block_quizchat_questions_form span[role="option"] div.divcontainer-questions').attr('title');
                let selected_participant_name = $('#block_quizchat_instructor_form div.participant-name-menu').attr('title');
                let allgrouptxt = group_string + ' ' + gname;
                if(participantid != quizchat_address_question_group)
                 //selected_participant_name != allgrouptxt
                {
                    //remove the selected participant
                    $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                    $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                    observeForElement('#block_quizchat_instructor_form li');
                }
                else {
                    if(selected_participant_name != allgrouptxt) {
                        $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                        observeForElement('#block_quizchat_instructor_form li');
                    }
                }
            }
        }
        else {//there is selected question but no participant is selected
            if(questionid == String(quizchat_general_question_id)) {//general question 0
                //remove the selected participant
                $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                observeForElement('#block_quizchat_instructor_form li');
            }
            else {//question selected
                //select question group in participants
                $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                observeForElement('#block_quizchat_instructor_form li');
            }
        }
    } else {
        if(selected_participant) {
            $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
        }
    }
} }
};

const observeForElement = (elementSelector) => {
    const callback = (mutationsList, observer) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'childList') {
                let targetElement = document.querySelector(elementSelector);
                if (targetElement) {
                    // Element found, perform actions
                    if (elementSelector.includes('block_quizchat_instructor_form') && noparticipant_flag) {
                        users_select_change();
                    }
                    if(elementSelector.includes('li')) {
                        if($(elementSelector).closest('ul').css('display') !== 'none') {
                            // if(elementSelector.includes('block_quizchat_instructor_form li[data-value')) {
                            //     li_clicked = true;
                            // }
                            if($(elementSelector.includes('block_quizchat_instructor_form li[data-value'))) {
                                li_clicked = true;
                            }
                            $(elementSelector).first().attr('aria-selected', 'true');
                            $(elementSelector).first().trigger('click');
                            // add_divcontainerquestions();
                            // Stop observing since the element is found
                            observer.disconnect();
                            // if(($('#fitem_id_block_quizchat_users_select span[role="option"]').attr('data-value') == pid)&&
                            // elementSelector.includes('block_quizchat_instructor_form')) {
                            //     li_clicked = false;
                            // }
                            break;
                        }
                    }
                    else {
                        $(elementSelector).trigger('click');
                        // add_divcontainerquestions();
                        // Stop observing since the element is found
                        observer.disconnect();
                        break;
                    }
                }
            }
        }
    };

    // Create a new MutationObserver instance
    const observer = new MutationObserver(callback);

    // Start observing the DOM
    observer.observe(document.body, {
        childList: true, // Listen for added/removed child nodes
        subtree: true    // Observe the entire subtree of the body
    });
};

const add_divcontainer_after_question_select = () => {
    let targetElement = document.querySelector('#fitem_id_block_quizchat_questions_select span[role="option"][data-value="'
    + quizchat_general_question_id + '"]');
    if (targetElement) {
        // The element exists in the DOM
        add_divcontainerquestions();
        //clearTimeout(timeoutid);
    } else {
        // The element is not yet rendered
        // Schedule another check after a delay
        setTimeout(() => {
            add_divcontainer_after_question_select();
        }, 150); // Check again later
    }
};

const setFormsIfExist = () => {
    let q_form = document.querySelector('#block_quizchat_questions_form ul.form-autocomplete-suggestions');
    let p_form = document.querySelector('#block_quizchat_instructor_form ul.form-autocomplete-suggestions');
    if (q_form && p_form) {
        setMenus();
        clearTimeout(timeoutid);
    } else {
        // The element is not yet rendered
        // Schedule another check after a delay
        timeoutid = setTimeout(setFormsIfExist, 1000); // Check again after 1 second
    }
};

const click_private_group_btns = () => {
    if (quizchat_msg.messages.length > 0) {
        $('#allmsgs_link').trigger("click");
        $('#btn_msg_me').trigger("click");
        $('#btn_msg_all').trigger("click");
        clearTimeout(timeoutid_private_group_btns);
    } else {
        // The element is not yet rendered
        // Schedule another check after a delay
        // Check again after 1 second
        if(!$('.header-container').html().trim()) {
            update_conversation_header(allmsgs_id,false);
        }
        timeoutid_private_group_btns = setTimeout(click_private_group_btns, 1000);
    }
};

const resetMenus = () => {
    $('#block_quizchat_questions_form span[role="option"]').each(function() {
        // replace the existing content with HTML content
        $(this).find('.divcontainer-questions').replaceWith(htmlContent_questions);
    });
    $('#block_quizchat_questions_form span[role="option"]').attr('data-value',quizchat_general_question_id);
    $('#block_quizchat_questions_form span[role="option"]').parent().attr('data-active-value',quizchat_general_question_id);
    $('#block_quizchat_instructor_form span[role="option"]').each(function() {
        // Check if the current span contains a statecircle-base-menu and remove it
        if ($(this).find('.statecircle-base-menu').length > 0) {
            // Remove it
            $(this).find('.statecircle-base-menu').remove();
        }
        // replace the existing content with HTML content
        $(this).find('.divcontainer').replaceWith(htmlContent_participants);
    });
    $('#block_quizchat_instructor_form span[role="option"]').attr('data-value',quizchat_address_everyone);
    $('#block_quizchat_instructor_form span[role="option"]').parent().attr('data-active-value',quizchat_address_everyone);
    $('#id_block_quizchat_questions_select').empty();
    $('#id_block_quizchat_questions_select').append(htmlContent_q_select);
    $('#id_block_quizchat_users_select').empty();
    $('#id_block_quizchat_users_select').append(htmlContent_p_select);
};

const resetMenus_click_respond = (pid, qid, qtxt) => {
    let questionid;
    let participantid;
    let selected_question = document.querySelector('#fitem_id_block_quizchat_questions_select span[role="option"]');
    let selected_participant = document.querySelector('#fitem_id_block_quizchat_users_select span[role="option"]');
    if(selected_question)
    {
        questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
        if (selected_participant) {
            participantid = $('#fitem_id_block_quizchat_users_select span[role="option"]')[0].dataset.value;
            if(questionid == String(qid)) {//the desired question is already selected :)
                if(participantid != String(pid)) {
                    // allow_active_li = true;
                    $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                    observeForElement('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
                }
            }
            else {//question selected (not the desired question) and there is selected participant
                //remove the selected question
                click_to_respond(qid,qtxt);
                noparticipant_flag = true;
                $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                observeForElement('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
            }
        }
        else {//there is selected question but no participant is selected
            if(questionid == String(qid)) {//the desired question is already selected :)
                //select the desired person in participants
                // allow_active_li = true;
                $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                observeForElement('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
            }
            else {//a question is selected (not the desired question) and there is no selected participant
                click_to_respond(qid,qtxt);
                // allow_active_li = true;
                $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                observeForElement('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
            }
        }
    } else {//no selected question
        if(selected_participant) {
            $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
        }
        click_to_respond(qid,qtxt);
        // allow_active_li = true;
        //$('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
        //observeForElement('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
        waitForElement('#fitem_id_block_quizchat_questions_select span[role="option"]', () => {
            observeSelectedQuestion(qid, pid);
        });
    }
    if(qid == quizchat_address_everyone &&
     $('#fitem_id_block_quizchat_questions_select span[role="option"]').attr('data-value') != qid){
        add_divcontainer_after_question_select();
    }
    noparticipant_flag = false;
};

// Utility function to wait for an element to exist in the DOM
const waitForElement = (selector, callback) => {
    const element = document.querySelector(selector);
    if (element) {
        callback();
    } else {
        const observer = new MutationObserver(() => {
            const element = document.querySelector(selector);
            if (element) {
                observer.disconnect();
                callback();
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
};

const observeSelectedQuestion= (qid, pid) => {
    const targetElement = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0];
    // Check if the data-value matches the qid
    if (targetElement.dataset.value === qid) {
        // Execute the steps
        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
        observeForElement('#block_quizchat_instructor_form li[data-value="'+pid+'"]');
    }
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

const click_to_respond = (questionid, questiontxt) => {
    let select_list_q = $('#block_quizchat_questions_form ul.form-autocomplete-suggestions');
    let existing_list_item_q = $('li[role="option"][data-value="' + questionid + '"]', select_list_q);
    let listitem_q = '<li role="option" data-value="' + questionid + '"></li>';
    let menu_select_q = $('#id_block_quizchat_questions_select');
    let existing_option_q = $('option[value="' + questionid + '"', menu_select_q);
    let option_q = '<option value="' + questionid + '"></option>';
    let divcontainer_q = $('<div class="divcontainer-questions"></div>');
    let childdiv_q = '<div class="questions-menu" title="' + questiontxt + '">' + questiontxt + '</div>';
    let li_to_click_q = '#block_quizchat_questions_form ul.form-autocomplete-suggestions li[role="option"][data-value="'
                    + questionid + '"]';
    click_to_respond_process (select_list_q, existing_list_item_q, listitem_q, menu_select_q, existing_option_q,
        option_q, divcontainer_q, childdiv_q, li_to_click_q);
};

const click_to_respond_process = (select_list, existing_list_item, listitem, menu_select,
     existing_option, option, divcontainer, childdiv, li_to_click) => {
    divcontainer.append(childdiv);
    if(0 === existing_list_item.length) {
        select_list.append($(listitem).append(divcontainer));
    }
    if(0 === existing_option.length) {
        menu_select.append($(option).append(divcontainer));
    }
    $(li_to_click).trigger('click');
};

const setMenus = () => {
    htmlContent_participants = '<div title="' + lang_strings['everyone']
    + '" class="divcontainer"><div class="participant-name-menu" title="'
    + lang_strings['everyone'] + '">'
    + lang_strings['everyone'] + '</div></div>';
    htmlContent_questions = '<div title="' + lang_strings['student_question_general']
            + '" class="divcontainer-questions"><div class="questions-menu" title="'
            + lang_strings['student_question_general'] + '">'
            + lang_strings['student_question_general'] + '</div></div>';
    htmlContent_q_select = $('#id_block_quizchat_questions_select').children().clone();
    htmlContent_p_select = $('#id_block_quizchat_users_select').children().clone();
    add_divcontainerquestions();
    $('#block_quizchat_instructor_form span[role="option"]').each(function() {
        // replace the existing content with HTML content
        $(this).contents().last().replaceWith(htmlContent_participants);
    });
    $('#block_quizchat_questions_form span[role="option"]').attr('data-value',quizchat_general_question_id);
    $('#block_quizchat_questions_form span[role="option"]').parent().attr('data-active-value',quizchat_general_question_id);
    $('#block_quizchat_instructor_form span[role="option"]').attr('data-value',quizchat_address_everyone);
    $('#block_quizchat_instructor_form span[role="option"]').parent().attr('data-active-value',quizchat_address_everyone);
};

const help_click = () => {
    $('#rolepermission_link').on('click', function(event) {
            event.preventDefault();
            // Get the href attribute value
            let hrefValue = $('#rolepermission_link').attr('href');
            let newwindow = window.open(hrefValue,'rolepermission_link',
            '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes,width=600,height=600');
            if (window.focus) {newwindow.focus();}
        });
    };

const fullscreen_click = (event) => {
            event.preventDefault();
            // Get the href attribute value
            let hrefValue = $('.fullscreen_actionmenu_item').attr('href');
            window.open(hrefValue, '_blank', 'toolbar=yes,scrollbars=yes,resizable=yes');
    };

const ul_callback = (mutations) => {
    for (var mutation of mutations) {
        if (mutation.type === 'childList') {
            let attr = $('#block_quizchat_questions_form ul.form-autocomplete-suggestions').attr('aria-hidden');
            if (typeof attr == 'undefined' || attr == false) {
                let chosen_option_span = $('#block_quizchat_questions_form span[role="option"]');
                if(chosen_option_span.length > 0) {
                    let active_li = $('#block_quizchat_questions_form ul li[aria-selected="true"]');
                    if($(active_li).length > 0) {
                        if(parseInt($(active_li).attr('data-value')) !== parseInt($(chosen_option_span).attr('data-value'))) {
                            // Menu is visible, trigger deselection of the selected item
                            $('#block_quizchat_questions_form ul.form-autocomplete-suggestions li[aria-selected="true"]')
                            .attr('aria-selected', 'false');
                        }
                    }

                }
            }
        }
    }
};

const ul_callback_p = (mutations) => {
    for (var mutation of mutations) {
        if (mutation.type === 'childList') {
            let attr = $('#block_quizchat_instructor_form ul.form-autocomplete-suggestions').attr('aria-hidden');
            if (typeof attr == 'undefined' || attr == false) {
                let chosen_option_span = $('#block_quizchat_instructor_form span[role="option"]');
                if(chosen_option_span.length > 0) {
                    let active_li = $('#block_quizchat_instructor_form ul li[aria-selected="true"]');
                    if($(active_li).length > 0) {
                        if(parseInt($(active_li).attr('data-value')) !== parseInt($(chosen_option_span).attr('data-value'))
                        ) {
                            if(!li_clicked) {
                                // Menu is visible, trigger deselection of the selected item
                            $('#block_quizchat_instructor_form ul.form-autocomplete-suggestions li[aria-selected="true"]')
                            .attr('aria-selected', 'false');
                            }
                            else {
                                li_clicked = false;
                            }
                        }
                    }

                }
            }
        }
    }
};

const toggle_autocomplete = (enable, parentElement) => {
    if (parentElement.length) {
        if(enable) {
            if(parentElement.is(':hidden')) {
                parentElement.attr('style','display:block !important;');
            }
        } else {
            if(!parentElement.is(':hidden')) {
                parentElement.attr('style','display:none !important;');
            }
        }
    }
};

export const sidemenu_conv_link_clicked = (event) => {
    event.preventDefault();
    // href is '#userid
    const anchorElement = event.target.closest('a');
    const userid= anchorElement.getAttribute('href').substring(1);
    write_sessionStorage('selected_user_or_question_id', userid);
    write_sessionStorage('grp_flag', false);
    update_conversation_header(userid,false);
    $('#block_quizchat_messages > .block_quizchat_msg_area_body').empty();
    resetMenus();
    toggle_autocomplete(true, $('#fitem_id_block_quizchat_questions_select'));
    toggle_autocomplete(false, $('#fitem_id_block_quizchat_users_select'));
    write_sessionStorage('total_unread_msg_usr_qs', 0);
    let p_user = quizchat_msg.p_users.find(el => el.userid === parseInt(userid));
    let msg_ids_str = p_user.message_ids.replace(/\s/g, '').split(',');
    let msg_ids = msg_ids_str.map(id => parseInt(id));
    let filteredMessages = quizchat_msg.messages.filter(msg => msg_ids.includes(msg.id));
    update_current_msgs_array(filteredMessages);
    let key = 'newmsgscount_userid_'+ userid;
    let msgs_count = int_sessionStorage(key);
    write_sessionStorage('latest_msg_id_usr_qs', msg_ids[msg_ids.length - msgs_count- 1]);
    update_msg_area(filteredMessages.length);
    write_sessionStorage('latest_msg_id_usr_qs', msg_ids[msg_ids.length - 1]);
    write_sessionStorage('newmsgscount_all', int_sessionStorage('newmsgscount_all') - int_sessionStorage(key));
    write_sessionStorage(key, '0');
    // Second parameter is true if the first one is question id
    newmsgs_count_notf(userid, false);
};

export const sidemenu_gconv_link_clicked = (event) => {
    event.preventDefault();
    // href is '#question_id
    const anchorElement = event.target.closest('a');
    const questionid= anchorElement.getAttribute('href').substring(1);
    write_sessionStorage('selected_user_or_question_id', questionid);
    write_sessionStorage('grp_flag', true);
    update_conversation_header(questionid,true);
    $('#block_quizchat_messages > .block_quizchat_msg_area_body').empty();
    toggle_autocomplete(false, $('#fitem_id_block_quizchat_questions_select'));
    toggle_autocomplete(false, $('#fitem_id_block_quizchat_users_select'));
    write_sessionStorage('total_unread_msg_usr_qs', 0);
    let group = quizchat_msg.groups.find(el => el.question_id === parseInt(questionid));
    let msg_ids_str = group.message_ids.replace(/\s/g, '').split(',');
    let msg_ids = msg_ids_str.map(id => parseInt(id));
    let filteredMessages = quizchat_msg.messages.filter(msg => msg_ids.includes(msg.id));
    update_current_msgs_array(filteredMessages);
    let key = 'newmsgscount_questionid_'+ questionid;
    let msgs_count = int_sessionStorage(key);
    write_sessionStorage('latest_msg_id_usr_qs', msg_ids[msg_ids.length - msgs_count- 1]);
    update_msg_area(filteredMessages.length);
    write_sessionStorage('latest_msg_id_usr_qs', msg_ids[msg_ids.length - 1]);
    write_sessionStorage('newmsgscount_all', int_sessionStorage('newmsgscount_all') - int_sessionStorage(key));
    write_sessionStorage(key, '0');
    // Second parameter is true if the first one is question id
    newmsgs_count_notf(questionid, true);
};

export const allmsgs_link_clicked = (event) => {
    event.preventDefault();
    // href is '#question_id
    update_conversation_header(allmsgs_id,false);
    $('#block_quizchat_messages > .block_quizchat_msg_area_body').empty();
    resetMenus();
    toggle_autocomplete(true, $('#fitem_id_block_quizchat_questions_select'));
    toggle_autocomplete(true, $('#fitem_id_block_quizchat_users_select'));
    write_sessionStorage('selected_user_or_question_id', allmsgs_id);
    write_sessionStorage('grp_flag', false);
    update_current_msgs_array(quizchat_msg.messages);
    let msgs = quizchat_msg.messages;
    update_msg_area(msgs.length);
    Object.keys(sessionStorage).forEach(key => {
        if (key.includes('newmsgscount_')) {
            sessionStorage.setItem(key, '0');
        }
    });
    $('span[id*="unread-count-question-"], span[id*="unread-count-user-"]').each(function() {
        let span_id = $(this).attr('id');
        let parts = span_id.split('-');
        let second_last_part = parts[parts.length - 2];
        let question_or_user_id = parseInt(parts[parts.length - 1]);
        let is_question = second_last_part === 'question';
        newmsgs_count_notf(question_or_user_id, is_question);
    });
};

export const newmsgs_count_notf = (question_or_user_id, is_questionid) => {
    let span_id = '#unread-count-' + (is_questionid ? 'question' : 'user') +'-' + String(question_or_user_id);
    let key = 'newmsgscount_'+(is_questionid ? 'questionid' : 'userid')+'_'+ String(question_or_user_id);
    let msgs_count = int_sessionStorage(key);
    let header_question_or_user_id, header_is_allmsgs = false;
    if($('#conv-header').data('group-id') !== undefined) {
        header_question_or_user_id = parseInt($('#conv-header').data('group-id'));
        if(header_question_or_user_id == allmsgs_id)
        {
            header_is_allmsgs = true;
        }
    }
    else if($('#conv-header').data('user-id') !== undefined){
        header_question_or_user_id = parseInt($('#conv-header').data('user-id'));
    }
    if(msgs_count > 0) {
        let key_all = 'newmsgscount_all';
        let session_msgs_count_all = 0;
        if(parseInt($(span_id).html()) !== msgs_count) {
            $(span_id).html(String(msgs_count));
            $(span_id).attr('aria-hidden','false');
            $(span_id).parent('span').removeClass('hidden');
        }
        Object.keys(sessionStorage).forEach(sessionkey => {
            if (sessionkey.includes('newmsgscount_questionid_') || sessionkey.includes('newmsgscount_userid_')) {
                session_msgs_count_all += parseInt(sessionStorage.getItem(sessionkey));
            }
        });
        write_sessionStorage(key_all, String(session_msgs_count_all));
        allmsgs_count_notf();
        //update allmsgs session
        if(header_is_allmsgs) {
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_')) {
                    sessionStorage.setItem(sessionkey, '0');
                }
            });
        }
        else {
            if(header_question_or_user_id == question_or_user_id) {
                write_sessionStorage(key, '0');
            }
            session_msgs_count_all = 0;
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_questionid_') || sessionkey.includes('newmsgscount_userid_')) {
                    session_msgs_count_all += parseInt(sessionStorage.getItem(sessionkey));
                }
            });
            write_sessionStorage(key_all, String(session_msgs_count_all));
        }
    }
    else {
        $(span_id).attr('aria-hidden','true');
        $(span_id).parent('span').addClass('hidden');
        $(span_id).html('');
    }
};

const update_conversation_header = (id, groupFlag) => {
    $('.header-container').empty();
    let ar = (id==allmsgs_id?[]:(groupFlag?quizchat_msg.groups:quizchat_msg.p_users));
    let element = (id==allmsgs_id?[]:ar.find(el =>
        (groupFlag ? el.question_id === parseInt(id) : el.userid === parseInt(id))
    ));
    if(typeof element == 'undefined') {
        element = quizchat_users.find(u => u.id === parseInt(id));
        element.picture = element.profileimageurlsmall;
        element.userid = element.id;
    }
    let conv_header =
    $('<div class="bg-white position-relative border-bottom p-1 px-sm-2" data-region="view-conversation" data-from-panel="true">'+
        '<div class="" data-region="header-content">'+
            '<div class="d-flex align-items-center">'+
                '<div class="d-flex text-truncate">'+
                        '<div class="d-flex align-items-center">'+
                            '<img class="rounded-circle" '+
                            'src="'+
                            (id==allmsgs_id?grp_img_white_grey:
                            (groupFlag?(element.question_id == quizchat_address_everyone?grp_img_white_grey:
                            grp_img_white):element.picture))+
                            '" alt="'+
                            (id==allmsgs_id?'All messages':
                            (groupFlag?element.group_name:element.fullname))+'" aria-hidden="true" style="height: 38px">'+
                            '<span id="conv-header" class="contact-status-header icon-size-2 " '+
                            (id==allmsgs_id?'data-group-id="'+allmsgs_id:
                            (groupFlag?'data-group-id="'+element.question_id:'data-user-id="'+element.userid))+
                            '">'+
                            (groupFlag||id==allmsgs_id?' ':'<div id="header-state" class="statecircle-base '+element.state+'" '+
                            'data-user-id="'+element.userid +
                            '" title="'+lang_strings[element.state]+'"></div>')+'</span>'+
                        '</div>'+
                        '<div class="w-100 text-truncate ml-2">'+
                            '<div class="d-flex">'+
                                '<strong class="m-0 text-truncate">'+
                                (id==allmsgs_id?'All messages':(groupFlag?element.group_name:element.fullname))+
                                '</strong>'+
                            '</div>'+
                            //All messages description
                            '<p id="written-header-state" class="m-0 font-weight-light text-truncate">'+
                            (id==allmsgs_id?' ':
                            //Group messages description
                            (groupFlag?' ':lang_strings[element.state]))+
                            '</p>'+
                        '</div>'+
                '</div>'+
            '</div>'+
        '</div>'+
    '</div>');
    $('.header-container').append(conv_header);
};

export const btn_msg_me_click = () => {
    if(typeof quizchat_msg.p_users != 'undefined') {
        let new_msgs_count, msgs_count, key;
        $('#conversations_container_private').empty();
        let msgs = quizchat_msg;
        if(typeof quizchat_msg_counters !== 'undefined') {
            msgs = quizchat_msg_counters;
        }
        msgs.p_users.forEach(user => {
        new_msgs_count = (typeof user.new_msgs_count == 'undefined'? 0 : user.new_msgs_count);
        key = 'newmsgscount_userid_'+ String(user.userid);
        msgs_count = sessionStorage.getItem('moodle_qc_' + quizchat_userid + '_' + quizchatid + '_' + key);
        if(null === msgs_count) {
            write_sessionStorage(key, String(new_msgs_count));
            msgs_count = new_msgs_count;
        }
        else {
            msgs_count = int_sessionStorage(key) + new_msgs_count;
            write_sessionStorage(key, String(msgs_count));
        }
        // Split the message_ids string into an array and get the last item
        let messageIdsArray = user.message_ids.split(', ');
        let lastMessageId = messageIdsArray[messageIdsArray.length - 1];
        let user_last_msg = quizchat_msg.messages.filter(msg => (msg.id == lastMessageId));
        let msg_time = new Date(user_last_msg[0].timestamp * 1000);
        let displayDate = isToday(user_last_msg[0].timestamp) ?
        msg_time.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : msg_time.toLocaleDateString("en-GB");
            let conv_link =
            $('<a href="#'+user.userid
            +'" class="py-0 px-2 d-flex list-group-item list-group-item-action align-items-center" '+
            'data-conversation-id="-" '+
            'data-user-id="'+user.userid+'" role="button" id="side-menu-conv-link-user-'+String(user.userid)+'">'+
            '<img class="rounded-circle align-self-start mt-2" '+
            'src="'+user.picture+'" '+
            'title="'+user.fullname+'" '+
            'alt="'+user.fullname+'" aria-hidden="true" style="height: 38px">'+
                    '<span class="contact-status-sidemenu icon-size-2 " '+'data-user-id="'+user.userid+'">'+
                    '<div class="statecircle-base '+user.state+'" title="'+lang_strings[user.state]+'"></div>'+
                    '</span>'+
                    '<div class="w-100 text-truncate ml-2 my-2">'+
                        '<div class="d-flex">'+
                            '<strong class="m-0 text-truncate">'+user.fullname+'</strong>'+
                            '<span class="hidden" data-region="contact-icon-blocked">'+
                                '<i class="icon fa fa-ban fa-fw " title="Contact blocked" '
                                +'role="img" aria-label="Contact blocked"></i>'+
                            '</span>'+
                            '<span class="hidden" data-region="muted-icon-container">'+
                                '<i class="icon fa fa-microphone-slash fa-fw " aria-hidden="true"></i>'+
                            '</span>'+
                        '</div>'+
                        '<p class="m-0 font-weight-light text-truncate last-message" data-region="last-message">'
                        +(user_last_msg[0].userid == quizchat_userid?
                            lang_strings['sidemenu_you']+': <span>'+user_last_msg[0].message+'</span>':
                        '<span>'+user_last_msg[0].message+'</span>')
                        +'</p>'+
                    '</div>'+
                    '<div class="d-flex align-self-stretch">'+
                        '<div class="px-2 py-1 small position-absolute position-right " '+
                        'data-region="last-message-date" aria-hidden="true">'+ displayDate +'</div>'+
                        '<div class="d-flex align-self-center align-items-center">'+
                            '<span class="badge rounded-pill bg-primary text-white '+
                            (msgs_count > 0 ? '' :'hidden')
                            +'" data-region="unread-count">'+
                                '<span id="unread-count-user-'+ user.userid +'" data-user-id="'+ user.userid +'">'
                                + (msgs_count > 0 ? String(msgs_count) :'')
                                +'</span>'+
                            '</span>'+
                            '<div class="text-muted ml-auto">'+
                                '<span class="dir-rtl-hide">'+
                                    '<i class="icon fa fa-chevron-right fa-fw " aria-hidden="true"></i>'+
                                '</span>'+
                                '<span class="dir-ltr-hide">'+
                                    '<i class="icon fa fa-chevron-left fa-fw " aria-hidden="true"></i>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</a>');
            $('#conversations_container_private').append(conv_link);
            // Second parameter is false if the first one is user id
            newmsgs_count_notf(user.userid, false);
        });
        let header_question_or_user_id, header_is_allmsgs = false;
        if($('#conv-header').data('group-id') !== undefined) {
            header_question_or_user_id = parseInt($('#conv-header').data('group-id'));
            if(header_question_or_user_id == allmsgs_id)
            {
                header_is_allmsgs = true;
            }
        }
        else if($('#conv-header').data('user-id') !== undefined){
            header_question_or_user_id = parseInt($('#conv-header').data('user-id'));
        }
        if(header_is_allmsgs) {
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_')) {
                    sessionStorage.setItem(sessionkey, '0');
                }
            });
        }
        else {
            let session_msgs_count_all = 0;
            let key_all = 'newmsgscount_all';
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_questionid_') || sessionkey.includes('newmsgscount_userid_')) {
                    session_msgs_count_all += parseInt(sessionStorage.getItem(sessionkey));
                }
            });
            write_sessionStorage(key_all, String(session_msgs_count_all));
            allmsgs_count_notf();
        }
        $('#conversations_container_private').off('click', 'a', sidemenu_conv_link_clicked);
        $('#conversations_container_private').on('click','a', sidemenu_conv_link_clicked);
    }
};

export const btn_msg_grp_click = () => {
    if(typeof quizchat_msg.groups != 'undefined') {
        let new_msgs_count, msgs_count, key;
        $('#conversations_container_group').empty();
        let msgs = quizchat_msg;
        if(typeof quizchat_msg_counters !== 'undefined') {
            msgs = quizchat_msg_counters;
        }
        msgs.groups.forEach(group => {
        new_msgs_count = (typeof group.new_msgs_count == 'undefined'? 0 : group.new_msgs_count);
        key = 'newmsgscount_questionid_'+ String(group.question_id);
        msgs_count = sessionStorage.getItem('moodle_qc_' + quizchat_userid + '_' + quizchatid + '_' + key);
        if(null === msgs_count) {
            write_sessionStorage(key, String(new_msgs_count));
            msgs_count = new_msgs_count;
        }
        else {
            msgs_count = int_sessionStorage(key) + new_msgs_count;
            write_sessionStorage(key, String(msgs_count));
        }
        // Split the message_ids string into an array and get the last item
        let messageIdsArray = group.message_ids.split(',');
        let lastMessageId = messageIdsArray[messageIdsArray.length - 1];
        let group_last_msg = quizchat_msg.messages.filter(msg => (msg.id == lastMessageId));
        let msg_time = new Date(group_last_msg[0].timestamp * 1000);
        let displayDate = isToday(group_last_msg[0].timestamp) ?
        msg_time.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) : msg_time.toLocaleDateString("en-GB");
            let conv_link =
            $('<a href="#'+group.question_id
            +'" class="py-0 px-2 d-flex list-group-item list-group-item-action align-items-center" '+
            'data-conversation-id="-" '+
            'data-group-id="'+group.question_id+'" role="button" id="side-menu-conv-link-question-'+String(group.question_id)+'">'+
            '<img class="rounded-circle align-self-start mt-2" '+
            'src="'+(group.question_id == quizchat_address_everyone?grp_img_white_grey:
                grp_img_white) +'" '
            +'title="'+group.group_name+'" '
            +'alt="'+group.group_name+'" aria-hidden="true" style="height: 38px">'+
                    '<span class="contact-status icon-size-2 ">'+
                    '</span>'+
                    '<div class="w-100 text-truncate ml-2 my-2">'+
                        '<div class="d-flex">'+
                            '<strong class="m-0 text-truncate">'+group.group_name+'</strong>'+
                            '<span class="hidden" data-region="contact-icon-blocked">'+
                                '<i class="icon fa fa-ban fa-fw " title="Contact blocked" '
                                +'role="img" aria-label="Contact blocked"></i>'+
                            '</span>'+
                            '<span class="hidden" data-region="muted-icon-container">'+
                                '<i class="icon fa fa-microphone-slash fa-fw " aria-hidden="true"></i>'+
                            '</span>'+
                        '</div>'+
                        '<p class="m-0 font-weight-light text-truncate last-message" data-region="last-message">'
                        +(group_last_msg[0].userid == quizchat_userid?
                            lang_strings['sidemenu_you']+': <span>'+group_last_msg[0].message+'</span>':
                        '<span>'+group_last_msg[0].message+'</span>')
                        +'</p>'+
                    '</div>'+
                    '<div class="d-flex align-self-stretch">'+
                        '<div class="px-2 py-1 small position-absolute position-right " '+
                        'data-region="last-message-date" aria-hidden="true">'+displayDate+'</div>'+
                        '<div class="d-flex align-self-center align-items-center">'+
                            '<span class="badge rounded-pill bg-primary text-white '+
                            (msgs_count > 0 ? '' :'hidden')
                            +'" data-region="unread-count">'+
                                '<span id="unread-count-question-'+ group.question_id
                                +'" data-question-id="'+ group.question_id +'">'
                                + (msgs_count > 0 ? String(msgs_count) :'')
                                +'</span>'+
                            '</span>'+
                            '<div class="text-muted ml-auto">'+
                                '<span class="dir-rtl-hide">'+
                                    '<i class="icon fa fa-chevron-right fa-fw " aria-hidden="true"></i>'+
                                '</span>'+
                                '<span class="dir-ltr-hide">'+
                                    '<i class="icon fa fa-chevron-left fa-fw " aria-hidden="true"></i>'+
                                '</span>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</a>');
            $('#conversations_container_group').append(conv_link);
            // Second parameter is true if the first one is question id
            newmsgs_count_notf(group.question_id, true);
        });
        let header_question_or_user_id, header_is_allmsgs = false;
        if($('#conv-header').data('group-id') !== undefined) {
            header_question_or_user_id = parseInt($('#conv-header').data('group-id'));
            if(header_question_or_user_id == allmsgs_id)
            {
                header_is_allmsgs = true;
            }
        }
        else if($('#conv-header').data('user-id') !== undefined){
            header_question_or_user_id = parseInt($('#conv-header').data('user-id'));
        }
        if(header_is_allmsgs) {
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_')) {
                    sessionStorage.setItem(sessionkey, '0');
                }
            });
        }
        else {
            let session_msgs_count_all = 0;
            let key_all = 'newmsgscount_all';
            Object.keys(sessionStorage).forEach(sessionkey => {
                if (sessionkey.includes('newmsgscount_questionid_') || sessionkey.includes('newmsgscount_userid_')) {
                    session_msgs_count_all += parseInt(sessionStorage.getItem(sessionkey));
                }
            });
            write_sessionStorage(key_all, String(session_msgs_count_all));
            allmsgs_count_notf();
        }
        $('#conversations_container_group').off('click', 'a', sidemenu_gconv_link_clicked);
        $('#conversations_container_group').on('click','a', sidemenu_gconv_link_clicked);
    }
};

export const init_instructor = (arg_quizchat, confingsetting_msglen, reqmsg, receivergroups) => {
    is_teacher = true;
    import('block_quizchat/master').then(() => {
        quizchatobj = arg_quizchat;
        msglen = confingsetting_msglen;
        requiredmsg = reqmsg;
        groups = receivergroups;
        $('#id_block_quizchat_users_select').change(() => {
            // reset style of select label on correct selection
            $('#block_quizchat_instructor_form label').css({'color': '#000', 'font-weight': 'normal'});
        });
        $('#id_block_quizchat_questions_select').on('change', questions_select_change);
        $('#id_block_quizchat_users_select').on('change', users_select_change);
        $('#id_block_quizchat_users_select').on('click', users_select_change);
        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').on('click', users_select_change);
        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').on('change', users_select_change);
        $('#block_quizchat_instructor_form').submit(send_msg);
        $('#permission-link').on('click', help_click);
        $('.fullscreen_actionmenu_item').on('click', fullscreen_click);
        $(textinputcontrolname).on('input',text_oninput);
        $(textinputcontrolname).on('blur',text_onblur);
        $(textinputcontrolname).on('invalid',text_setvalidmsg);
        setFormsIfExist();
        let targetNode = $('#block_quizchat_questions_form');
        let targetNode_p = $('#block_quizchat_instructor_form');
        let ul_config = {childList: true, subtree: true};
        let ul_observer = new MutationObserver(ul_callback);
        let ul_observer_participants = new MutationObserver(ul_callback_p);
        ul_observer.observe(targetNode[0], ul_config);
        ul_observer_participants.observe(targetNode_p[0], ul_config);
        if(!full_screen_flag) {
            write_sessionStorage('selected_user_or_question_id', "-2");
            write_sessionStorage('grp_flag', false);
        }
        $('#block_quizchat_input_instructor_send').trigger('focus');
        if($('#btn_msg_me')) {
            $('#btn_msg_me').on('click', btn_msg_me_click);
        }
        if($('#btn_msg_all')) {
            $('#btn_msg_all').on('click', btn_msg_grp_click);
        }
        if($('#allmsgs_link')) {
            $('#allmsgs_link').on('click', allmsgs_link_clicked);
        }
        if(full_screen_flag) {
            click_private_group_btns();
        }
    });
};
