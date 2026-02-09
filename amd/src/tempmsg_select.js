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

export const list = (query,quizchatid, centraltempflag) => {
    return fetchMany([{
        methodname: 'block_quizchat_get_template_messages',
        args: {
            templateid: null,
            onlyenabled: true,
            quizchatid: quizchatid,
            partial_name: query,
            centraltempflag: centraltempflag
        }
    }])[0].then(response => {
        // response.data is array of arrays
        const data = response || [];
        // Define keys
        const keys = ["actions", "title", "message", "status", "user", "date", "id", "isenabled"];
        // Convert to array of objects
        const objects = data.map(item => Object.fromEntries(keys.map((key, i) => [key, item[i]])));

        return objects;
    });
};

export const processResults = (selector, results) => {
    var options = [];
    results.forEach(temp => {
        options.push({
            value: String(temp.id) + '" ' + 'data-tempmsg="' + temp.message.replace(/<[^>]*>/g, '').trim() + '"' ,
            label: temp.title.replace(/<[^>]*>/g, '').trim()
        });
    });
    return options;
};

export const transport = (selector, query, callback) => {
    const qcInput = document.getElementById('id_block_quizchat_quizchatid');
    const centraltemp = document.getElementsByName('block_quizchat_usecentraltempmsgs')[0];
    const quizchatid = qcInput ? parseInt(qcInput.value) : null;
    const centraltempflag = centraltemp ? parseInt(centraltemp.value) : null;
    list(query, quizchatid, centraltempflag).then(callback).catch(Notification.exception);
};
