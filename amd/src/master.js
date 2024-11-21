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
import {add as addToast} from 'core/toast';
import $ from 'jquery';
import {queue_typeset as cardbodyMathJax} from 'block_quizchat/load-mathjax';
import * as Instructor from 'block_quizchat/instructor';
import {
    autofill_users_select,
    update_user_select_status_indicators,
    btn_msg_me_click,
    btn_msg_grp_click
} from 'block_quizchat/instructor';

export var poll_timeout = 10000;
var poll_timeout_id;
export var poll_timeout_id_fullscreen;
var unnotify_timeout;
var unnotify_timeout_id;
export var quizchatid = 0;
export var quizchat_userid = 0;
let processedDates = {};
export var quizchat_msg = {
    "messages": []
};
export var quizchat_msg_prev = {
    "messages": []
};
export var quizchat_msg_usr_or_qs = {
    "messages": []
};
export var quizchat_msg_counters = {
    "messages": []
};
export var quizchat_users = [];

const init_quizchat_users = (users = []) => {
    quizchat_users = [
        {
            // The everyone 'group'
            'id': quizchat_address_everyone,
            'lastname': lang_strings['everyone'],
            'firstname': '(' + lang_strings['group'] + ')',
            'fullname': lang_strings['everyone'],
            'state': 'finished'
        },
        {
            // The instructors 'group'
            'id': quizchat_address_instructors,
            'lastname': lang_strings['instructors'],
            'firstname': '(' + lang_strings['group'] + ')',
            'fullname': lang_strings['instructors'],
            'state': 'finished'
        }
    ];
    if (Array.isArray(users) && 0 < users.length) {
        quizchat_users.push(...users.filter(u => { if(![0, 1].includes(u.id)) { return u; } } ));
    }
};

export const push_quizchat_users = ( users = [] ) => {
    let new_users = users.filter( u => {
        let i = quizchat_users.findIndex( qc_u => qc_u.id === u.id );
        if( -1 === i ) {
            // In case that user is not yet known from previous messages
            // return it to be pushed onto the quizchat_users array
            return u;
        } else {
            // if that user is indeed already known to the quizchat_users array
            // update that entry with fresh data
            if (![quizchat_address_everyone, quizchat_address_instructors].includes(i)) {
                if (typeof u.state !== 'undefined')
                {
                    quizchat_users[i] = u;
                }
            }
        }
    });
    quizchat_users.push(...new_users);
};

const enrolled_states = ['abandoned','inprogress','noattempt','finished'];

// Make sure notifications and msg highlighting do not occur
// due to simple page reload
var page_reloaded = true;
var getmsgs_mostrecentmsg_id = 0;
var most_recent_msg_id = 0;

export let lang_strings = {};

export const quizchat_address_everyone = 0;
export const quizchat_address_instructors = -1;
export const quizchat_address_question_group = -2;
export const quizchat_student_question_id = -1;
export const quizchat_general_question_id = 0;
export const allmsgs_id = -1;

export var no_drawer = false;
export var full_screen_flag;

// use this in other modules to restart polling
export const reset_poll_timeout = () => {
    clearTimeout(poll_timeout_id);
    poll_messages(quizchatid);
};

export const reset_poll_timeout_fullscreen = () => {
    clearTimeout(poll_timeout_id_fullscreen);
    poll_messages(quizchatid);
};

export const reset_timeoutid_fullscreen = () => {
    poll_timeout_id_fullscreen = setTimeout(poll_messages, poll_timeout, quizchatid);
};

export const int_sessionStorage = (key) => {
    let val = sessionStorage.getItem('moodle_qc_' + quizchat_userid + '_' + quizchatid + '_' + key);
    if(null === val) {
        return 0;
    } else if (val == "true") {
        return true;
    }else if (val == "false") {
        return false;
    }else if (val == "-1") {
        return -1;
    } else {
        return parseInt(val);
    }
};

export const write_sessionStorage = (key, val) => {
    sessionStorage.setItem('moodle_qc_' + quizchat_userid + '_' + quizchatid + '_' + key, val);
};

export const poll_messages = (quizchatid) => {
    const followUp = (data) => {
        let user_or_qs_id = int_sessionStorage('selected_user_or_question_id');
        let group_session = int_sessionStorage('grp_flag');
        most_recent_msg_id = (!full_screen_flag || user_or_qs_id == -1 ? int_sessionStorage('latest_msg_id')
        : int_sessionStorage('latest_msg_id_usr_qs'));
        getmsgs_mostrecentmsg_id = 0;
        let user_or_question, usr_or_ques_msgs, usr_or_ques_msgs_str;
        if(full_screen_flag && user_or_qs_id >= 0) {
            user_or_question = (group_session ? data.groups.find(
                el => el.question_id === user_or_qs_id
            )
            : data.p_users.find(el => el.userid === user_or_qs_id));
            if(typeof user_or_question != 'undefined') {
                usr_or_ques_msgs_str = user_or_question.message_ids.replace(/\s/g, '');
                usr_or_ques_msgs_str = usr_or_ques_msgs_str.split(',');
                usr_or_ques_msgs = usr_or_ques_msgs_str.map(id => parseInt(id));
            }
            else {
                usr_or_ques_msgs = [];
            }
        }
        let filteredMessages, msgs_length;
        if(full_screen_flag && user_or_qs_id >= 0) {
            if(usr_or_ques_msgs.length > 0) {
                // Filter the messages where the id is present in usr_or_ques_msgs
                filteredMessages = data.messages.filter(msg => usr_or_ques_msgs.includes(msg.id));
            } else {
                filteredMessages = [];
            }
        }
        else {
            filteredMessages = data.messages;
        }
        msgs_length = filteredMessages.length;
        if(msgs_length > 0)
        {
            let total_unread_msg = int_sessionStorage('total_unread_msg');
            let diff = filteredMessages.filter(msg => most_recent_msg_id < msg.id).length;
            total_unread_msg += filteredMessages.filter(
                msg => (most_recent_msg_id < msg.id) && (msg.userid !== quizchat_userid)).length;
            // Only update notification when a new msg arrives,
            // even if unread messages remain.
            if(most_recent_msg_id < filteredMessages[msgs_length -1].id || page_reloaded) {
                if(!full_screen_flag || user_or_qs_id == -1) {
                    write_sessionStorage('total_unread_msg', total_unread_msg);
                } else {
                    write_sessionStorage('total_unread_msg_usr_qs', 0);
                }
                // Assign fresh data to global quizchat_msg
                quizchat_msg_prev = quizchat_msg;
                quizchat_msg = data;
                quizchat_msg_counters = compare_actual_and_prev_data(quizchat_msg_prev, quizchat_msg);
                if(full_screen_flag && user_or_qs_id >= 0) {quizchat_msg_usr_or_qs = filteredMessages;}
                if(page_reloaded){
                    page_reloaded = false;
                    update_msg_area(msgs_length);
                } else {
                    update_msg_area(diff);
                }
                update_notification(filteredMessages[msgs_length -1]);
                if(!full_screen_flag || user_or_qs_id == -1){
                    write_sessionStorage('latest_msg_id', filteredMessages[msgs_length -1].id);
                } else {
                    write_sessionStorage('latest_msg_id_usr_qs', filteredMessages[msgs_length -1].id);
                }
            }
            else {
                quizchat_msg_prev = quizchat_msg;
                quizchat_msg = data;
                quizchat_msg_counters = compare_actual_and_prev_data(quizchat_msg_prev, quizchat_msg);
                if(full_screen_flag && user_or_qs_id >= 0){
                    quizchat_msg_usr_or_qs = filteredMessages;
                }
                update_message_headers();
                update_sidemenu_status();
                update_header_status();
                if((typeof $('#conversations_container_private').html()) != 'undefined') {
                    btn_msg_me_click();
                }
                if((typeof $('#conversations_container_group').html()) != 'undefined') {
                    btn_msg_grp_click();
                }
            }
        }
        else if (msgs_length == 0) {
            empty_private_group_msgs_count();
            quizchat_msg_prev = quizchat_msg;
            quizchat_msg = data;
            quizchat_msg_counters = compare_actual_and_prev_data(quizchat_msg_prev, quizchat_msg);
            if(full_screen_flag && user_or_qs_id >= 0){
                quizchat_msg_usr_or_qs = filteredMessages;
            }
            update_message_headers();
            update_sidemenu_status();
            update_header_status();
            if((typeof $('#conversations_container_private').html()) != 'undefined') {
                btn_msg_me_click();
            }
            if((typeof $('#conversations_container_group').html()) != 'undefined') {
                btn_msg_grp_click();
            }
        }
    };
    const calls = [
        {
            methodname: 'block_quizchat_get_messages',
            args: {
                "quizchatid": quizchatid,
                "most_recent_msg_id": getmsgs_mostrecentmsg_id,
                "langstr_general" : lang_strings['student_question_general'],
                "langstr_group" : lang_strings['group_txt'],
                "langstr_attempt" : lang_strings['quiz_attempt_txt'],
                "langstr_all" : lang_strings['everyone'],
                "langstr_strftimerecentfull" : lang_strings['strftimerecentfull']
            }
        }
    ];
    fetchMany(calls)[0]
        .then(
            data => {
                if(0 < data.stats.msg_total){
                    data.messages.sort(
                        (a,b) => {
                            return (a.timestamp > b.timestamp);
                        }
                    );
                    let userdata=[];
                    const usersMap = new Map();
                    data.messages.forEach(message => {
                        // Extract sender user data
                        if (!usersMap.has(message.userid)) {
                            usersMap.set(message.userid, {
                                id: message.userid,
                                lastname: message.lastname,
                                firstname: message.firstname,
                                fullname: message.fullname,
                                profileimageurlsmall: message.picture,
                                state: message.state
                            });
                        }
                    });
                    data.messages.forEach(message => {
                        if(message.receiverid != quizchat_address_question_group) {
                            // Extract receiver user data
                            if (!usersMap.has(message.receiverid)) {
                                usersMap.set(message.receiverid, {
                                    id: message.receiverid,
                                    lastname: message.rlastname,
                                    firstname: message.rfirstname,
                                    fullname: message.rfullname
                                });
                            }
                        }
                        else {
                            // Extract receiver user data
                            if (!usersMap.has(message.receiverid)) {
                                usersMap.set(message.receiverid + '/' + message.rfullname, {
                                    id: message.receiverid + '/' + message.rfullname,
                                    lastname: message.rlastname,
                                    firstname: message.rfirstname,
                                    fullname: message.rfullname
                                });
                            }
                        }
                    });
                    userdata = Array.from(usersMap.values());
                    if(0 < userdata.length){
                        push_quizchat_users(userdata);
                        // Could this be done nicer?
                        // Async nature of things seems to make this necessary
                        followUp(data);
                    } else {
                        followUp(data);
                    }
                }
                else if(0 > data.stats.msg_total){
                    followUp(data);
                    empty_private_group_msgs_count();
                }
        });
    poll_timeout_id = setTimeout(poll_messages, poll_timeout, quizchatid);
};

const empty_private_group_msgs_count = () => {
    if($('#private_msgs_count')) {
        $('#private_msgs_count').html("0");
    }
    if($('#group_msgs_count')) {
        $('#group_msgs_count').html("0");
    }
};

export const allmsgs_count_notf = () => {
    let key = 'newmsgscount_all';
    let msgs_count = sessionStorage.getItem('moodle_qc_' + quizchat_userid + '_' + quizchatid + '_' + key);
    if(null === msgs_count) {
        write_sessionStorage(key, '0');
        msgs_count = 0;
    }
    else {
        msgs_count = int_sessionStorage(key);
    }
    if(msgs_count > 0 ) {
        $('#all-messages-unread-count').html(String(msgs_count));
        $('#all-messages-unread-count').attr('aria-hidden','false');
        $('#all-messages-unread-count-label').removeClass('hidden');
    }
    else {
        $('#all-messages-unread-count').attr('aria-hidden','true');
        $('#all-messages-unread-count-label').addClass('hidden');
        $('#all-messages-unread-count').html('');
    }
};

export const getNewMessageCount = (prev, current) => {
    const prevMessages = prev ? prev.split(',').map(Number) : [];
    const currentMessages = current ? current.split(',').map(Number) : [];
    let filteredmsgs = quizchat_msg.messages.filter(msg => currentMessages.includes(msg.id));
    const newMessages = filteredmsgs.filter(msg => !prevMessages.includes(msg.id) &&
    msg.id > int_sessionStorage('latest_msg_id') && (msg.userid != quizchat_userid));
    return newMessages.length;
};

export const calculateUserMessageCounts = (prevUsers, currUsers) => {
    return currUsers.map(currUser => {
        const prevUser = prevUsers.find(u => u.userid === currUser.userid);
        const new_msgs_count = prevUser
            ? getNewMessageCount(prevUser.message_ids, currUser.message_ids)
            : currUser.message_ids.replace(/\s/g, '').split(',').length; // All messages are new for a new user
        // Determine if there is a new conversation
        const new_conversation = !prevUser || new_msgs_count > 0;
        return { ...currUser, new_msgs_count, new_conversation };
    });
};

export const calculateGroupMessageCounts = (prevGroups, currGroups) => {
    return currGroups.map(currGroup => {
        const prevGroup = prevGroups.find(g => g.question_id === currGroup.question_id);
        const new_msgs_count = prevGroup
            ? getNewMessageCount(prevGroup.message_ids, currGroup.message_ids)
            : currGroup.message_ids.replace(/\s/g, '').split(',').length; // All messages are new for a new group
        // Determine if there is a new conversation
        const new_conversation = !prevGroup || new_msgs_count > 0;
        return { ...currGroup, new_msgs_count, new_conversation };
    });
};

export const compare_actual_and_prev_data = (prevData, currData) => {
    const newUserCounts = calculateUserMessageCounts(prevData.p_users || currData.p_users, currData.p_users);
    const newGroupCounts = calculateGroupMessageCounts(prevData.groups || currData.groups, currData.groups);
    return {
        p_users: newUserCounts,
        groups: newGroupCounts
    };
};

export const update_current_msgs_array = (current_msgs) => {
    if(current_msgs.length > 0) {
        quizchat_msg_usr_or_qs = current_msgs;
        most_recent_msg_id = current_msgs[current_msgs.length - 1].id;
        processedDates = {};
    }
};

export const update_msg_area = (diff) => {
    let msgs, last_received_msg_id;
    if(int_sessionStorage('selected_user_or_question_id') >= 0) {
        msgs = quizchat_msg_usr_or_qs;
        last_received_msg_id = int_sessionStorage('latest_msg_id_usr_qs');
    } else if (!full_screen_flag || most_recent_msg_id > 0) {
        msgs = quizchat_msg.messages;
        last_received_msg_id = most_recent_msg_id;
    }
    if(typeof msgs != 'undefined') {
        let new_msg = msgs.slice(msgs.length - diff);
        let profimg = '';
        let new_msg_user, new_msg_receiver, card_flavor, new_msg_el, msg_time;
        if (0 < diff) {
            // Append only new messages to msg area
            for (let i = 0; i < new_msg.length; i++) {
                new_msg_user = quizchat_users.find(u => u.id == new_msg[i].userid);
                //group messaging
                if (new_msg[i].receiverid == quizchat_address_question_group) {
                    new_msg_receiver = quizchat_users.find(u => u.id == new_msg[i].receiverid + '/' + new_msg[i].rfullname);
                } else {
                    new_msg_receiver = quizchat_users.find(u => u.id == new_msg[i].receiverid);
                }
                card_flavor = new_msg[i].userid === quizchat_userid
                    ? 'bg-secondary'
                    : 'bg-light border border-secondary'
                        + (last_received_msg_id < new_msg[i].id ? ' font-weight-bolder' : '');
                new_msg_el = $('<div class="card block_quizchat_msg_el ' + card_flavor
                + ' mb-1" data-msg-id="' + new_msg[i].id + '"></div>');
                msg_time = new Date(new_msg[i].timestamp * 1000);
                profimg = '<div class="imgcontainer"><img class="rounded profileimg" src="'
                + new_msg_user.profileimageurlsmall
                + '" alt="' + new_msg_user.fullname + '" title="' + new_msg_user.fullname + '"></img></div>';
                let displayDate = isToday(new_msg[i].timestamp) ? lang_strings['today'] : new_msg[i].date_part;
                // Check if a div for this date already exists
                if (!processedDates[displayDate]) {
                    // If not, create a new div for this date
                    let dateDiv = $('<div class="line-with-text" id="'
                    + displayDate + '"><hr><span>' + displayDate + '</span><hr></div>');
                    $('#block_quizchat_messages > .block_quizchat_msg_area_body').append(dateDiv);
                    processedDates[displayDate] = displayDate;
                }
                $(new_msg_el)
                .append(
                    $('<div class="card-header"></div>')
                    .append(
                        $('<div class="msg-header p-0"></div>')
                        .append(
                            $('<div class="block_quizchat_user_icon">')
                            .append(profimg),
                            $(
                                '<div class="text-right tofrom from">'
                                + lang_strings['from'] + ':</div>'
                            ),
                            $(
                                '<div class="fullname text-truncate" data-address-type="from"'
                                + ' title="' + new_msg_user.fullname + '">'
                                + new_msg_user.fullname + '</div>'
                            ),
                            $(
                                '<div class="timestamp text-right">'
                                + msg_time.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" }) + '</div>'
                            ),
                            $(
                                '<div class="text-right tofrom to">'
                                + lang_strings['to'] + ':</div>'
                            ),
                            $(
                                '<div class="fullname text-truncate" data-address-type="to"'
                                + ' title="' + new_msg_receiver.fullname + '">'
                                + new_msg_receiver.fullname + '</div>'
                            )
                        )
                    ),
                    $(
                        '<div class="card-body"><div class="question-info"><b>'
                        + lang_strings['student_question_select'] + ' ' + new_msg[i].questiontxt
                        + new_msg[i].quizattempt +'</b></div>'
                        + '<div class="msg-txt">' + new_msg[i].message +'</div>'
                        + '</div>'
                    )
                );
                if($('#private_msgs_count')) {
                    $('#private_msgs_count').html(quizchat_msg.stats.private);
                }
                if($('#group_msgs_count')) {
                    $('#group_msgs_count').html(quizchat_msg.stats.group);
                }
                $('#block_quizchat_messages > .block_quizchat_msg_area_body').append(new_msg_el);
                //trigger MathJax to render equations within the element with the class "card-body"
                if (i == new_msg.length-1){
                    cardbodyMathJax('card-body');
                    // Scroll to bottom of msg area
                    $('.block_quizchat_msg_area_body').scrollTop($('.block_quizchat_msg_area_body')[0].scrollHeight);
                }
            }
            if(((typeof $('#conversations_container_private').html()) != 'undefined')) {
                    btn_msg_me_click();
            }
            if((typeof $('#conversations_container_group').html()) != 'undefined') {
                    btn_msg_grp_click();
            }
            // Will unnotify in case drawer is open
            update_unnotify_timeout();
            update_message_headers();
            update_sidemenu_status();
            update_header_status();
            update_user_select_status_indicators();
        }
    }
};

const update_notification = (msg) => {
    let total_unread_msg = int_sessionStorage('total_unread_msg');
    if(( quizchat_userid !== msg.userid ) && ( 0 < total_unread_msg )){
        let diff_string = total_unread_msg + ' ' +
            (1 < total_unread_msg || 0 == total_unread_msg
                ? lang_strings['notification_new_msg_plural']
                : lang_strings['notification_new_msg_singular']
            );
        $('.toast').remove();
        addToast(msg.message, {
            type: 'info',
            title: diff_string,
            subtitle: lang_strings['from'] + ': ' + quizchat_users.find(u => u.id === msg.userid).fullname,
            closeButton: true,
            autohide: false,
            delay: 10000,
        })
        .then(() => {
            if (!no_drawer && !full_screen_flag) {
                update_toast_style($('#theme_boost-drawers-blocks')[0]);
            }
            cardbodyMathJax('toast-wrapper');
        });
        $('#block_quizchat_messages > .block_quizchat_msg_area_header').text(diff_string);
        if(!full_screen_flag)
        {
            $('#new_msg_icon').remove();
            $('.drawer-toggler.drawer-right-toggle').prepend(
                $('<img src="' + M.cfg.wwwroot
                    + '/blocks/quizchat/img/icon-new-message-30.png"'
                    + 'id="new_msg_icon" alt="' + diff_string + '" title="' + diff_string + '"></img>')
            );
        }
    }
};

export const isToday = (date) => {
    let today = new Date();
    let checkDate = new Date(date * 1000);

    // Reset time part to compare only dates
    today.setHours(0, 0, 0, 0);
    checkDate.setHours(0, 0, 0, 0);

    return today.getTime() === checkDate.getTime();
};

const unnotify = () => {
    write_sessionStorage('total_unread_msg', 0);
    $('.toast').remove();
    $('.block_quizchat_msg_el.font-weight-bolder').removeClass('font-weight-bolder');
    if(full_screen_flag)
    {
        allmsgs_count_notf();
        let header_question_or_user_id, header_is_allmsgs = false, side_counter_span_id;
        if($('#conv-header').data('group-id') !== undefined) {
            header_question_or_user_id = parseInt($('#conv-header').data('group-id'));
            if(header_question_or_user_id == allmsgs_id)
            {
                header_is_allmsgs = true;
            }
            side_counter_span_id = '#unread-count-question-'+header_question_or_user_id;
        }
        else if($('#conv-header').data('user-id') !== undefined){
            header_question_or_user_id = parseInt($('#conv-header').data('user-id'));
            side_counter_span_id = '#unread-count-user-'+header_question_or_user_id;
        }
        if(header_is_allmsgs){
            $('span[id^="unread-count-"]').attr('aria-hidden','true');
            $('span[id^="unread-count-"]').parent('span').addClass('hidden');
            $('span[id^="unread-count-"]').html('');
        }
        else {
            $(side_counter_span_id).attr('aria-hidden','true');
            $(side_counter_span_id).parent('span').addClass('hidden');
            $(side_counter_span_id).html('');
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
    } else {
        $('#block_quizchat_messages > .block_quizchat_msg_area_header').text('0 ' + lang_strings['notification_new_msg_plural']);
    }
    $('#new_msg_icon').remove();
};

const update_toast_style = (drawer) => {
    let toast_width = '350';
    let button = document.querySelector('[data-toggler="drawers"][data-target="theme_boost-drawers-blocks"][data-action="toggle"]');
    let offset = 0;
    // No drawers in SEB
    if (!no_drawer) {
        if (drawer.classList.contains('show')) {
            offset = drawer.offsetWidth + 15;
        } else {
            offset = button.parentNode.offsetWidth + 15;
        }
    }
    $('.toast').removeClass('mx-auto');
    $('.toast').css({
        'position': 'absolute',
        'right': offset,
        'width': toast_width,
        'max-width': toast_width
    });
    // Make the small subtitle with the 'from' field truncate too long names
    $('.toast .toast-subtitle.ml-auto.small').css({
        'white-space': 'nowrap',
        'max-width': '200px',
        'overflow': 'hidden',
        'text-overflow': 'ellipsis',
        'text-align': 'right'
    });
    $('.toast .toast-message').css({
        'white-space': 'nowrap',
        'overflow': 'hidden',
        'text-overflow': 'ellipsis'
    });
    $('.toast-wrapper').removeClass(['mx-auto', 'fixed-top']);
    $('.toast-wrapper').css({
        'position': 'absolute',
        'width': toast_width,
        'max-width': toast_width,
        'right': '0'
    });
};

const create_respond_link = (userid, fullname, questionid, questiontxt) => {
    //'#userid/questionid/questiontxt'
    let respond_link = document.createElement('a');
    respond_link.setAttribute('href', '#' + userid + '/' + questionid + '/' + questiontxt);
    respond_link.setAttribute('id', 'click_to_respond_link_' + userid + '_' + questionid + '_' + questiontxt);
    respond_link.append(document.createTextNode(fullname));
    respond_link.addEventListener('click', autofill_users_select);
    return respond_link;
};

const update_message_headers = () => {
    $('.block_quizchat_msg_el').each(function(i, el) {
        let msg;
        if(quizchat_msg.messages.length==1)
        {
            msg = quizchat_msg.messages[0];
        }
        else
        {
            msg = quizchat_msg.messages.find(m => m.id === parseInt(el.getAttribute('data-msg-id')));
        }
        let msg_user = quizchat_users.find(u => u.id === msg.userid);
        // Add or remove respond link depending on user state
        if (msg.userid !== quizchat_userid) {
            let fullname_th = $('div.fullname[data-address-type="from"]', $(el));
            fullname_th.empty();
            if (enrolled_states.includes(msg_user.state)) {
                fullname_th.removeClass('fullname_suspended');
                if (Instructor.is_teacher) {
                    let $questionlinkobj = $(msg.questiontxt);
                    // Extract the text inside the <a> tag
                    let questiontxt = $questionlinkobj.text();
                    if(questiontxt == '') {
                        questiontxt = msg.questiontxt;
                    }
                    fullname_th.html(create_respond_link(msg.userid, msg_user.fullname, msg.questionid, questiontxt));
                } else {
                    fullname_th.text(msg_user.fullname);
                }
            } else {
                if (['suspended', 'deleted', 'unenrolled'].includes(msg_user.state)) {
                    fullname_th.addClass('fullname_suspended');
                }
                fullname_th.text(msg_user.fullname);
            }
        }
        if (Instructor.is_teacher) {
            // Insert state indicator where necessary
            // (user is instructor and indicator is not yet there)
            if ( $('div.imgcontainer div.statecircle-base', $(el)).length <= 0 ) {
                $('div.imgcontainer', $(el)).append('<div class="statecircle-base"></div>');
            }
            // Update state indicator depending on user state
            let state_indicator = $('div.imgcontainer div.statecircle-base', $(el));
            state_indicator.attr('title', lang_strings[msg_user.state]);
            state_indicator.removeClass();
            state_indicator.addClass('statecircle-base ' + msg_user.state);
        }
    });
};

const update_sidemenu_status = () => {
    $('.contact-status-sidemenu').each(function(i, el) {
        let user;
        if(quizchat_msg.p_users.length==1)
        {
            user = quizchat_msg.p_users[0];
        }
        else
        {
            user = quizchat_msg.p_users.find(u => u.userid === parseInt(el.getAttribute('data-user-id')));
        }
        if (Instructor.is_teacher) {
            // Update state indicator depending on user state
            let state_indicator = $('div.statecircle-base', $(el));
            state_indicator.attr('title', lang_strings[user.state]);
            state_indicator.removeClass();
            state_indicator.addClass('statecircle-base ' + user.state);
        }
    });
};

const update_header_status = () => {
    let state_indicator = $('#header-state');
    let written_state_indicator = $('#written-header-state');
    //update state indicator and written state
    if (Instructor.is_teacher && state_indicator.length && full_screen_flag && written_state_indicator.length) {
        let user = quizchat_msg.p_users.find(u => u.userid === parseInt(state_indicator.attr("data-user-id")));
        if(typeof user == 'undefined') {
            user = quizchat_users.find(u => u.id === parseInt(state_indicator.attr("data-user-id")));
        }
        state_indicator.attr('title', lang_strings[user.state]);
        state_indicator.removeClass();
        state_indicator.addClass('statecircle-base ' + user.state);
        written_state_indicator.html(lang_strings[user.state]);
    }
};

// Callback for mutations in drawer class list
const drawer_mutation_callback = (mutation) => {
    $('.block_quizchat_msg_area_body').scrollTop($('.block_quizchat_msg_area_body')[0].scrollHeight);
    update_unnotify_timeout();
    update_toast_style(mutation[0].target);
};

// separate function for this so it is callable from other functions
const update_unnotify_timeout = () => {
    if(!full_screen_flag) {
        if (no_drawer || $('#theme_boost-drawers-blocks')[0].classList.contains('show')) {
            unnotify_timeout_id = setTimeout(unnotify, unnotify_timeout);
        } else {
            clearTimeout(unnotify_timeout_id);
        }
    } else {
        unnotify_timeout_id = setTimeout(unnotify, unnotify_timeout);
    }
};

// setting_poll_timeout to be set in settings.php / admin interface
export const init = (arg_quizchat,arg_userid, setting_poll_timeout, setting_unnotify_timeout, no_drawer_flag,
     langstr_obj, fullscreen_flag) => {
    // The backend has checked for additional browser security
    // or a SEB config on this quiz
    no_drawer = no_drawer_flag;
    full_screen_flag = fullscreen_flag;
    // User Id is needed for client to ignore their own messages when
    // Updating notification
    quizchat_userid = arg_userid;
    lang_strings = { ...langstr_obj };
    // Initialize the quizchat_users array
    init_quizchat_users();
    const drawer_blocks = $('#theme_boost-drawers-blocks');
    const obs_config = { attributes: true, attributeFilter: ["class"] };
    const drawer_blocks_observer = new MutationObserver(drawer_mutation_callback);
    // Having been checked on the server side, these timeouts are safe
    poll_timeout = setting_poll_timeout * 1000;
    unnotify_timeout = setting_unnotify_timeout * 1000;
    // Drawers do not exist in SEB
    if (!no_drawer && !full_screen_flag) {
        drawer_blocks_observer.observe(drawer_blocks[0], obs_config);
    }
    quizchatid = arg_quizchat.id;
    poll_messages(quizchatid);
};

