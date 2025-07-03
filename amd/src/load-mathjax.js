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
import $ from 'jquery';
import * as mathjaxLoader from 'filter_mathjaxloader/loader';
import {
    notifyFilterContentRenderingComplete,
} from 'core_filters/events';

export var typeset_interval_id = { 'card-body': undefined, 'toast-wrapper': undefined };
export var typeset_msg_interval_id;
export var typeset_toast_interval_id;

// Queue the typeset and scroll msg area down where neccessary
export const queue_typeset = (classname) => {
    if(window.MathJax !== undefined) {
        if(window.MathJax.Hub !== undefined) {
            window.MathJax.Hub.Queue([
                "Typeset",
                window.MathJax.Hub, document.getElementsByClassName(classname),
                () => {
                    if('msg-txt' === classname) {//card-body
                        $('.block_quizchat_msg_area_body').scrollTop($('.block_quizchat_msg_area_body')[0].scrollHeight);
                    }
                }
            ]);
        } else {
            if (typeof mathjaxLoader.loadMathJax === 'function') {
                const elements = document.getElementsByClassName(classname);
                const elementArray = Array.from(elements);
                mathjaxLoader.loadMathJax().then(() => {
                    // Chain the calls to typesetPromise as it is recommended.
                    // https://docs.mathjax.org/en/v3.2-latest/web/typeset.html#handling-asynchronous-typesetting.
                    window.MathJax.startup.promise = window.MathJax.startup.promise
                        .then(() => window.MathJax.typesetPromise(elementArray))
                        .then(() => {
                            notifyFilterContentRenderingComplete(elementArray);
                            if('msg-txt' === classname) {//card-body
                                $('.block_quizchat_msg_area_body').scrollTop($('.block_quizchat_msg_area_body')[0].scrollHeight);
                            }
                        })
                        .catch();
                });
            } else {
                if(typeof window.MathJax.typeset === 'function') {
                    window.MathJax.typeset();
                    if('card-body' === classname) {
                        $('.block_quizchat_msg_area_body').scrollTop($('.block_quizchat_msg_area_body')[0].scrollHeight);
                    }
                }
            }
        }
    }
};
