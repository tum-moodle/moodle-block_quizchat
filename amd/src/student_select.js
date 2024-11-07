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
     push_quizchat_users,
     lang_strings
 } from 'block_quizchat/master';

 //let first_element_txt;
 //let first_element_value;
 let promise;

export const list = (quizid, everyonetxt, generaltxt, grouptxt, query) => {
    let selected_question = document.querySelector('#fitem_id_block_quizchat_questions_select span[role="option"]');
    let question_id = -1; // no selected question
    if(selected_question) {
        question_id = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
    }
    promise = fetchMany([{
        methodname: 'block_quizchat_get_users',
        args: {
            quizid: quizid,
            everyonetxt: everyonetxt,
            partial_name: query,
            questionid: parseInt(question_id),
            general_txt: generaltxt,
            group_txt: grouptxt
        }
    }]);
    return promise[0];
};

export const processResults = (selector, results) => {
    var options = [];
    push_quizchat_users(results);
    $.each(results, function(index, data) {
            let state = '';
            let statetag = '';
            if (data.state !== ''){
                state = lang_strings[data.state];
                statetag = '<div class="statecircle-base-menu ' + data.state + '" title = "' + state + '"></div>';
            }
            let name = data.lastname;
            if(data.firstname!=='') {
                name += ', ' + data.firstname;
            }
            let nametag='<div class="participant-name-menu" title="' + name + '">' + name + '</div>';
            let container = document.createElement("div");
            container.innerHTML = statetag + nametag;
            container.className = 'divcontainer';
            options.push({
                value: data.id,
                label: container,
                selected: (data.id == '0' || data.id == '-2') ? true : false,
            });
            /* if(data.id == '0' || data.id == '-2') {
                first_element_txt = name;
                first_element_value = data.id;
            } */
            //$('#block_quizchat_instructor_form ul.form-autocomplete-suggestions li:first-child').trigger('click');
            //$('#block_quizchat_instructor_form span[role="option"]').contents().last().replaceWith(first_element_txt);
            //$('#block_quizchat_instructor_form span[role="option"]').attr('data-value', first_element_value);
    });
    return options;
};

export const transport = (selector, query, callback) => {
    let quizid = $('[name="block_quizchat_quizid"]').val();
    let everyonetxt = $('[name="block_quizchat_langtxt_everyone"]').val();
    let generaltxt = $('[name="block_quizchat_general"]').val();
    let grouptxt = $('[name="block_quizchat_grouptxt"]').val();
    list(quizid, everyonetxt, generaltxt, grouptxt, query)
        .then(callback)
        .catch(Notification.exception);
    //alert(list_data);
    /* let group_string = lang_strings['group_txt']; //start here monday replace the old selection with the new one
    let everyone_string = lang_strings['everyone'];
    let questionid = 0;//general question
    if("" === $('#fitem_id_block_quizchat_questions_select option:selected')[0].value){
        // If a selection has been made and the search input gets focused and blurred again
        // without making a different selection the select element will have an empty
        // selected options property with no receiverid at all
        // => get selected option from span[role="option"]
        questionid = $('#fitem_id_block_quizchat_questions_select span[role="option"]')[0].dataset.value;
    } else {
        // The select element is ok, just get the value
        questionid = $('#id_block_quizchat_questions_select').val();
    }
    if(questionid == 0) {//general question
        //select everyone in participants
        remove_divcontainer_ifexists();
        $('#block_quizchat_instructor_form span[role="option"]').contents().last().replaceWith(everyone_string);
        $('#block_quizchat_instructor_form span[role="option"]').attr('data-value', quizchat_address_everyone);
    }
    else {//question selected
        //select question group in participants
        let gname = $('#block_quizchat_questions_form span[role="option"] div.divcontainer-questions').attr('title');
        remove_divcontainer_ifexists();
        $('#block_quizchat_instructor_form span[role="option"]').contents().last().replaceWith(group_string + ' ' + gname);
        $('#block_quizchat_instructor_form span[role="option"]').attr('data-value', quizchat_address_question_group);
    } */
};
