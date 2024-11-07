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
        quizchat_address_question_group,
        quizchat_general_question_id,
        quizchat_users,
        lang_strings
    } from 'block_quizchat/master';
import {
    checkCharsLength as checkMsgLength,
    resetCharsCount as updateCharsCount,
    handleWhiteSpaceMsg as checkWhiteSpaceMsg
} from 'block_quizchat/chars_limit';

let quizchatobj;
var msglen;
var requiredmsg;
var textinputcontrolname = '#block_quizchat_input_instructor_send';
var groupid = 0;//in case one to one message
var groups;
let timeoutid;
let htmlContent_participants;
let htmlContent_questions;
let htmlContent_q_select;
let htmlContent_p_select;
let noparticipant_flag = false;
export var is_teacher = false;

const send_msg = (submit_event) => {
    // Prevent default first
    submit_event.preventDefault();
    //if white space message
    if (checkWhiteSpaceMsg(textinputcontrolname))
    {
        let msg_text_input = document.querySelector(textinputcontrolname);
        updateCharsCount(msglen);
        msg_text_input.setCustomValidity(requiredmsg);
        msg_text_input.reportValidity();
        return -1;
    }
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
    let receiverid = $('#fitem_id_block_quizchat_users_select span[role="option"]')[0].dataset.value;
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
    let questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
    let grps_ar=Object.keys(groups).map(key => {
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
    $('#id_block_quizchat_enableflag').attr('value', 0);
    resetMenus_click_respond(userid, questionid, questiontxt);
    update_user_select_status_indicators();
    event.target.blur();
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
    if(enableflag == 1)
    {
    // Reset style of select label on correct selection
    $('#id_block_quizchat_questions_select_label').css({'color': '#000', 'font-weight': 'normal'});
    let group_string = lang_strings['group_txt'];
    let everyone_string = lang_strings['everyone'];
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
                    if($('#block_quizchat_instructor_form li[data-value="'+ quizchat_address_everyone + '"]').length == 0){
                        //remove the selected participant
                        $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                        //select everyone in participants
                        checkIfElementExists(
                            '#block_quizchat_instructor_form div.participant-name-menu[title="'+ everyone_string +'"]'
                        );
                    }
                    else {
                        $('#block_quizchat_instructor_form li[data-value="'+ quizchat_address_everyone + '"]').trigger('click');
                    }
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
                    if($('#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]').length == 0){
                        //remove the selected participant
                        $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                        checkIfElementExists(
                            '#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]'
                        );
                    }
                    else {
                        $('#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]').trigger('click');
                    }
                }
                else {
                    if(selected_participant_name != allgrouptxt) {
                        if($('#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]').length == 0){
                            $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
                            $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                            checkIfElementExists(
                                '#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]'
                            );
                        }
                        else
                        {
                            $('#block_quizchat_instructor_form div.participant-name-menu[title="'+ allgrouptxt + '"]')
                            .trigger('click');
                        }
                    }
                }
            }
        }
        else {//there is selected question but no participant is selected
            if(questionid == String(quizchat_general_question_id)) {//general question 0
                //remove the selected participant
                if($('#block_quizchat_instructor_form div.participant-name-menu[title="'+ everyone_string +'"]').length == 0) {
                    $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                    //select everyone in participants
                    checkIfElementExists(
                        '#block_quizchat_instructor_form div.participant-name-menu[title="'+ everyone_string +'"]'
                    );
                }
                else {
                    $('#block_quizchat_instructor_form div.participant-name-menu[title="'+ everyone_string +'"]').trigger('click');
                }
            }
            else {//question selected
                //select question group in participants
                let gname = $('#block_quizchat_questions_form span[role="option"] div.divcontainer-questions').attr('title');
                if(document.querySelector('#block_quizchat_instructor_form div.participant-name-menu[title="'
                + group_string + ' ' + gname + '"]')) {
                    $('#block_quizchat_instructor_form div.participant-name-menu[title="'+ group_string + ' ' + gname + '"]')
                    .trigger('click');
                }
                else {
                    $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                    checkIfElementExists(
                    '#block_quizchat_instructor_form div.participant-name-menu[title="'+ group_string + ' ' + gname + '"]');
                }
            }
        }
    } else {
        if(selected_participant) {
            $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
        }
    }
}
};

const checkIfElementExists = (element) => {
    if(element.includes('block_quizchat_instructor_form') && noparticipant_flag) {
        users_select_change();
    }
    let targetElement = document.querySelector(element);
    if (targetElement) {
        // The element exists in the DOM
        $(element).trigger('click');
        //add_divcontainerquestions();
        clearTimeout(timeoutid);
    } else {
        // The element is not yet rendered
        // Schedule another check after a delay
        timeoutid =setTimeout(() => {
            checkIfElementExists(element);
        }, 1000); // Check again later
    }
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
        timeoutid = setTimeout(setFormsIfExist, 1000); // Check again after 1 second (adjust delay as needed)
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
                    // change the participant menu only if the selected participant ! = the desired participant,
                    // otherwise keep the participant
                    if($('#block_quizchat_instructor_form li[data-value="'+ pid + '"]').length == 0)
                    {
                        $('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                        checkIfElementExists(
                            '#block_quizchat_instructor_form li[data-value="'+ pid + '"]'
                        );
                    } else {
                        $('#block_quizchat_instructor_form li[data-value="'+ pid + '"]').trigger('click');
                    }
                }
            }
            else {//question selected (not the desired question) and there is selected participant
                //remove the selected question
                click_to_respond(qid,qtxt);
                //todo
                    if($('#block_quizchat_instructor_form li[data-value="'+ pid + '"]').length == 0)
                    {
                        noparticipant_flag = true;
                        //$('#block_quizchat_instructor_form span.form-autocomplete-downarrow').trigger('click');
                        checkIfElementExists('#block_quizchat_instructor_form li[data-value="'+ pid + '"]');
                    }
                    else
                    {
                        $('#block_quizchat_instructor_form li[data-value="'+ pid + '"]').trigger('click');
                    }
            }
        }
        else {//there is selected question but no participant is selected
            if(questionid == String(qid)) {//the desired question is already selected :)
                //select the desired person in participants
                checkIfElementExists(
                    '#block_quizchat_instructor_form li[data-value="'+ pid + '"]'
                );
            }
            else {//a question is selected (not the desired question) and there is no selected participant
                click_to_respond(qid,qtxt);
                checkIfElementExists(
                    '#block_quizchat_instructor_form li[data-value="'+ pid + '"]'
                );
            }
        }
    } else {//no selected question
        if(selected_participant) {
            $('#block_quizchat_instructor_form span[aria-hidden="true"]:contains("× ")').trigger('click');
        }
        click_to_respond(qid,qtxt);
        checkIfElementExists(
            '#block_quizchat_instructor_form li[data-value="'+ pid + '"]'
        );
    }
    if(qid == quizchat_address_everyone &&
     $('#fitem_id_block_quizchat_questions_select span[role="option"]').attr('data-value') != qid){
        add_divcontainer_after_question_select();
    }
    noparticipant_flag = false;
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
                 // Menu is visible, trigger deselection of the selected item
                 $('#block_quizchat_questions_form ul.form-autocomplete-suggestions li[aria-selected="true"]')
                 .attr('aria-selected', 'false');
            }
        }
    }
};

const ul_callback_p = (mutations) => {
    for (var mutation of mutations) {
        if (mutation.type === 'childList') {
            let attr = $('#block_quizchat_instructor_form ul.form-autocomplete-suggestions').attr('aria-hidden');
            if (typeof attr == 'undefined' || attr == false) {
                 // Menu is visible, trigger deselection of the selected item
                 $('#block_quizchat_instructor_form ul.form-autocomplete-suggestions li[aria-selected="true"]')
                 .attr('aria-selected', 'false');
            }
        }
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
        $('#block_quizchat_input_instructor_send').trigger('focus');
    });
};
