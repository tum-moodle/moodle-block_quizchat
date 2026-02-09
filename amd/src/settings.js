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
import * as Notification from 'core/notification';
import * as Str from 'core/str';
import Ajax from 'core/ajax';
import {
    checkCharsLength as checkMsgLength,
    resetCharsCount as updateCharsCount,
    handleWhiteSpaceMsg as checkWhiteSpaceMsg
} from 'block_quizchat/chars_limit';
import $ from 'jquery';
import * as ModalEvents from 'core/modal_events';
let isInitialised = false;
let msglen;
let titlelen;
let templatesMap = {};
let currentSort = {
    column: 1,   // default: Title
    asc: true
};

// Runs ONCE per page load
export const init = () => {
    if (isInitialised) {
        return;
    }
    isInitialised = true;
    waitForElement('#quizchat-temp-msgs', () => {
            onFormOpen();
        });
    $(document).on(ModalEvents.shown, () => {
        waitForElement('#quizchat-temp-msgs', () => {
            onFormOpen();
        });
    });
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
//Runs EVERY time the edit form modal opens
const onFormOpen = () => {
    initQuiztempmsgsDiv ();
    const tabs = document.querySelectorAll(".tab-link");
    const contents = document.querySelectorAll(".tab-content");
    const saveCentral   = document.getElementById('quizchat-save-central');
    if (tabs.length) {
        tabs.forEach(tab => {
          if (!tab.dataset.bound) {
              tab.dataset.bound = "1";
              tab.addEventListener("click", () => {
                // Remove active classes
                tabs.forEach(t => t.classList.remove("active"));
                contents.forEach(c => c.classList.remove("active"));
                // Add active class to clicked tab
                tab.classList.add("active");
                // Activate target content
                const target = document.getElementById(tab.dataset.target);
                if (tab.dataset.target) {
                    target.classList.add("active");
                    if(tab.dataset.target === "quiztempmsgs") {
                        refreshTable();
                        resetForm();
                        const link   = document.getElementById('quizchat-createlink');
                        const form   = document.getElementById('quizchat-form');
                        form.style.display = 'none';
                        link.style.display = 'inline';
                    } else if(tab.dataset.target === "centraltempmsgs") {
                        refreshTable();
                    }
                }
              });
            }
        });
        if (!saveCentral.dataset.bound) {
            saveCentral.dataset.bound = "1";
            saveCentral.addEventListener('click', function(e) {
                e.preventDefault();
                saveBtn_CentralTempMsg();
            });
        }
        initTableHeadersLinks();
    } else {
        initTableHeadersLinks();
    }
};
const resetForm = () => {
    const title  = document.getElementById('quizchat-title');
    const msg    = document.getElementById('quizchat-message');
    const checkbox = document.getElementById('quizchat-ckbx_enabletemp');
    if (title) {title.value = '';}
    if (msg) {msg.value = '';}
    if (checkbox) {checkbox.checked = true;}
    updateCharsCount(msglen, '#charCount_msg');
    updateCharsCount(titlelen,'#charCount_title');
};
const sortTableByColumn = (tbody, columnIndex, asc = true) => {
    const rows = Array.from(tbody.querySelectorAll("tr"))
        .filter(tr => tr.children.length > columnIndex); // SAFETY
    const direction = asc ? 1 : -1;
    rows.sort((a, b) => {
        const cellA = a.children[columnIndex];
        const cellB = b.children[columnIndex];
        if (!cellA || !cellB) {return 0;}
        let A = cellA.textContent.trim();
        let B = cellB.textContent.trim();
        // Date comparison
        const dA = Date.parse(A);
        const dB = Date.parse(B);
        if (!isNaN(dA) && !isNaN(dB)) {
            return (dA - dB) * direction;
        }
        // Number comparison
        const nA = parseFloat(A);
        const nB = parseFloat(B);
        if (!isNaN(nA) && !isNaN(nB)) {
            return (nA - nB) * direction;
        }
        // String comparison
        return A.localeCompare(B, undefined, {
            numeric: true,
            sensitivity: "base"
        }) * direction;
    });
    rows.forEach(row => tbody.appendChild(row));
};
// Rebuild table from returned rows
const rebuildTable = (rows) => {
    // Check if the "centraltempmsgs" div exists
    const centralDiv = document.getElementById("centraltempmsgs");
    let tableBody, emptyMsg, activeDiv;
    if (!centralDiv) {
        tableBody = document.querySelector('#quizchat-temp-msgs .generaltable tbody');
        emptyMsg  = document.getElementById('empty_temp');
    } else {
        // Select the div that has both classes "tab-content" and "active"
        activeDiv = document.querySelector(".tab-content.active");
        let tableselector = '#'+ activeDiv.id + ' .generaltable tbody';
        tableBody = document.querySelector(tableselector);
        let emptytempid =  (activeDiv.id === "centraltempmsgs")?'empty_temp_central':'empty_temp';
        emptyMsg  = document.getElementById(emptytempid);
    }

    if (!tableBody) { return; }
    tableBody.innerHTML = ''; // clear existing
    if (!rows || rows.length === 0) {
        // Hide table, show empty message
        tableBody.parentElement.style.display = 'none';
        emptyMsg.style.display = 'block';
    } else {
        rows.forEach(row => {
            // row = [title, message, enabled, fullname, modified, actions]
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center">${row[0]}</td>
                <td>${row[1]}</td>
                <td>${row[2]}</td>
                <td class="text-center">${row[3]}</td>
                <td class="text-center">${row[4]}</td>
                <td class="text-center">${row[5]}</td>
            `;
            tableBody.appendChild(tr);
            if (!centralDiv || (activeDiv.id !== "centraltempmsgs")) {
                // attach event directly to the edit link within this row
                const editBtn = tr.querySelector('.edit_btn');
                if (editBtn) {
                    editBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const id = editBtn.dataset.id;
                        editTemplate(id);
                    });
                }
                // attach event directly to the delete link within this row
                const deleteBtn = tr.querySelector('.delete_btn');
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const id = deleteBtn.dataset.id;
                        deleteTemplate(parseInt(id));
                    });
                }
            } else {
                if(activeDiv.id === "centraltempmsgs") {
                    // attach event directly to the enable_cent_temp_chkbx checkbox within this row
                    const enblChkbx = tr.querySelector('.enable_cent_temp_chkbx');
                    if (enblChkbx) {
                        enblChkbx.addEventListener('change', (e) => {
                            e.preventDefault();
                            const id = enblChkbx.dataset.id;
                            checkedChanged(parseInt(id));
                        });
                    }
                }
            }
        });
        emptyMsg.style.display = 'none';
        tableBody.parentElement.style.display = 'block';
        initTableHeadersLinks();
    }
};
const initTableHeadersLinks = () => {
    const tempdiv= document.querySelector('#quizchat-temp-msgs');
    if (!tempdiv) {return;}
    const centralDiv = document.getElementById("centraltempmsgs");
    let tableBody, activeDiv, table;
    if (!centralDiv) {
        tableBody = document.querySelector('#quizchat-temp-msgs .generaltable tbody');
    } else {
        // Select the div that has both classes "tab-content" and "active"
        activeDiv = document.querySelector(".tab-content.active");
        let tableselector = '#'+ activeDiv.id + ' .generaltable tbody';
        tableBody = document.querySelector(tableselector);
    }
    if (!centralDiv) {
        table = document.querySelector('#quizchat-temp-msgs .generaltable');
    } else {
        // Select the div that has both classes "tab-content" and "active"
        activeDiv = document.querySelector(".tab-content.active");
        let tableselector = '#'+ activeDiv.id + ' .generaltable';
        table = document.querySelector(tableselector);
    }
    table.querySelector('thead')
            .querySelectorAll('.sort-asc, .sort-desc')
            .forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
    const headers = table.querySelectorAll('.generaltable th');
    headers.forEach((th, index) => {
        if (index === 0) {return;} // Action column disabled
        th.classList.add('sortable');
        if (!th.dataset.bound) {
            th.dataset.bound = "1";
            th.addEventListener('click', () => {
                // Toggle if same column, otherwise reset to ASC
                if (currentSort.column === index) {
                    currentSort.asc = !currentSort.asc;
                } else {
                    currentSort.column = index;
                    currentSort.asc = true;
                }
                // Update arrows
                table.querySelector('thead')
                    .querySelectorAll('.sort-asc, .sort-desc')
                    .forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
                th.classList.add(currentSort.asc ? 'sort-asc' : 'sort-desc');
                // Sort
                sortTableByColumn(tableBody, index, currentSort.asc);
            });
        }
        const defaultHeader = headers[1];
        currentSort = {
            column: 1,   // default: Title
            asc: true
        };
        defaultHeader.classList.add('sort-asc'); // show arrow
        sortTableByColumn(tableBody, currentSort.column, currentSort.asc);   // sort ascending
    });
};
const checkedChanged = (id) =>{
    // Find the checkbox by data-id
    const checkbox = document.querySelector(`.enable_cent_temp_chkbx[data-id='${id}']`);
    if (!checkbox) {return;}
    // Update the map
    templatesMap[id] = checkbox.checked ? 1 : 0;
};
const notify = async (show_notify = false) => {
    const msgstr = await Str.getString('template_saved', 'block_quizchat');
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }
    if(quizchatid === null) {
        // Trigger Moodle notification (this creates #user-notifications dynamically)
        Notification.addNotification({
            message: msgstr,
            type: 'success'
        });
    } else {
        const notice = document.getElementById('template-save-notice');
        if (!notice || !show_notify) {return;}
        notice.style.display = 'block';
        notice.style.opacity = '1';
        setTimeout(() => {
            notice.style.transition = 'opacity 0.5s';
            notice.style.opacity = '0';
            setTimeout(() => {
                notice.style.display = 'none';
                notice.style.transition = '';
            }, 500);
        }, 2000); // visible for 2 seconds
    }
};

// Fetch latest rows from service
const refreshTable = (shownotify=false) => {
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }

    // Check if the "centraltempmsgs" div exists
    const centralDiv = document.getElementById("centraltempmsgs");
    let centraltempflag, onlyenabled;
    let activeDiv;
    if (!centralDiv) {
        centraltempflag = false;
        onlyenabled = null;
    } else {
        // Select the div that has both classes "tab-content" and "active"
        activeDiv = document.querySelector(".tab-content.active");
        if(activeDiv) {
            if(activeDiv.id === 'centraltempmsgs') {
                //quizchatid = 0;
                centraltempflag = true;
                onlyenabled = false;
            } else {
                centraltempflag = false;
                onlyenabled = null;
            }
        }
    }
    const calls = [
        {
            methodname: 'block_quizchat_get_template_messages',
            args: {
                'templateid': null,
                'onlyenabled': onlyenabled,
                'quizchatid' : (quizchatid >0 ? quizchatid : null),
                'partial_name': '',
                'centraltempflag': centraltempflag
            }
        }
    ];
    fetchMany(calls)[0]
    .then((rows) => {
        rebuildTable(rows);
        notify(shownotify);
    })
    .catch();
};

const saveForm = async () => {
    const title  = document.getElementById('quizchat-title');
    const msg    = document.getElementById('quizchat-message');
    const checkbox = document.getElementById('quizchat-ckbx_enabletemp');
    const link   = document.getElementById('quizchat-createlink');
    const form   = document.getElementById('quizchat-form');
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }
    let requiredmsg = '';
    if (checkWhiteSpaceMsg('#quizchat-title') && checkWhiteSpaceMsg('#quizchat-message')) {
        requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        title.setCustomValidity(requiredmsg);
        msg.setCustomValidity(requiredmsg);
        title.reportValidity();
        msg.reportValidity();
        return;
    } else if (checkWhiteSpaceMsg('#quizchat-title')) {
        requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        title.setCustomValidity(requiredmsg);
        title.reportValidity();
        return;
    } else if (checkWhiteSpaceMsg('#quizchat-message')) {
        if(requiredmsg === '') {
            requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        }
        msg.setCustomValidity(requiredmsg);
        msg.reportValidity();
        return;
    }
    const calls = [
        {
            methodname: 'block_quizchat_create_template_message',
            args: {
                'title': title.value,
                'template': msg.value,
                'isenabled': checkbox.checked,
                'isquizlevel': (quizchatid >0 ? true : false),
                'templateid' : null,
                'quizchatid' : quizchatid
            }
        }
    ];
    fetchMany(calls)[0]
    .then( (data) => {
        resetForm();
        form.style.display = 'none';
        link.style.display = 'inline';
        refreshTable(true);
        return data.id;
    })
    .catch(() => {
        link.style.display = 'none';
        form.style.display = 'block';
    });
};

const saveBtn_CentralTempMsg = () => {
    let templates = [];
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }

    for (let id in templatesMap) {
        templates.push({
            templateid: parseInt(id),
            enabled: templatesMap[id],
            quizchatid: quizchatid
        });
    }
    const calls = [{
        methodname: 'block_quizchat_exclude_central_template_messages',
        args: {
            'templates': templates
        }
    }];

    fetchMany(calls)[0]
    .then(() => {
        refreshTable(true);
    })
    .catch(Notification.exception);
    //alert('save central temps clicked!');
};

 const unnotify = () => {
    const targetContainer = document.querySelector('#region-main');
    const notif = targetContainer.querySelector('#user-notifications');
    if (targetContainer && notif) {
        const closeButton = notif.querySelector('.btn-close');
        if (closeButton) {
            closeButton.click(); // simulate user click to close the notification
        }
    }
 };
 const editTemplate = (id) => {
    unnotify();
    const title  = document.getElementById('quizchat-title');
    const msg    = document.getElementById('quizchat-message');
    const checkbox = document.getElementById('quizchat-ckbx_enabletemp');
    const link   = document.getElementById('quizchat-createlink');
    const form   = document.getElementById('quizchat-form');
    const updateBtn   = document.getElementById('quizchat-update');
    const save   = document.getElementById('quizchat-save');
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }
    const calls = [{
        methodname: 'block_quizchat_get_template_messages',
        args: { templateid: id, onlyenabled: false , quizchatid: (quizchatid >0 ? quizchatid : null), partial_name: '',
             centraltempflag: false}
    }];

    fetchMany(calls)[0]
        .then((data) => {
            // Fill form with data
            title.value = data[0][1].replace(/<[^>]*>/g, '').trim(); // title
            msg.value = data[0][2].replace(/<[^>]*>/g, '').trim(); // template
            checkbox.checked = data[0][7]; // isenabled flag

            // Show form in edit mode
            updateBtn.dataset.id = id;
            save.style.display = 'none';
            updateBtn.style.display = 'inline-block';
            form.style.display = 'block';
            link.style.display = 'none';
            $('#quizchat-message').trigger('input');
            $('#quizchat-title').trigger('input');
        })
        .catch(Notification.exception);
};

// Function to update the template via WS
const updateTemplate = async (id) => {
    const title  = document.getElementById('quizchat-title');
    const msg    = document.getElementById('quizchat-message');
    const checkbox = document.getElementById('quizchat-ckbx_enabletemp');
    const link   = document.getElementById('quizchat-createlink');
    const form   = document.getElementById('quizchat-form');
    const qc_input = document.getElementById('id_block_quizchat_quizchatid');
    let quizchatid = null;

    if (qc_input) {
        quizchatid = parseInt(qc_input.value);
    } else {
        let temp_msgs_div = document.getElementsByName('quizchat-temp-msgs-edit-form')[0];
        if (temp_msgs_div) {
            quizchatid = temp_msgs_div.dataset.qcid;
        }
    }
    let requiredmsg = '';
    if (checkWhiteSpaceMsg('#quizchat-title') && checkWhiteSpaceMsg('#quizchat-message')) {
        requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        title.setCustomValidity(requiredmsg);
        msg.setCustomValidity(requiredmsg);
        title.reportValidity();
        msg.reportValidity();
        return;
    } else if (checkWhiteSpaceMsg('#quizchat-title')) {
        requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        title.setCustomValidity(requiredmsg);
        title.reportValidity();
        return;
    } else if (checkWhiteSpaceMsg('#quizchat-message')) {
        if(requiredmsg === '') {
            requiredmsg = await Str.getString('txtinput_required', 'block_quizchat');
        }
        msg.setCustomValidity(requiredmsg);
        msg.reportValidity();
        return;
    }
    const calls = [{
        methodname: 'block_quizchat_create_template_message',
        args: {
            'title': title.value,
            'template': msg.value,
            'isenabled': checkbox.checked,
            'isquizlevel': (quizchatid >0 ? true : false),
            'templateid' : parseInt(id),
            'quizchatid' : quizchatid
        }
    }];

    fetchMany(calls)[0]
        .then(() => {
            refreshTable(true);
            resetForm();
            form.style.display = 'none';
            link.style.display = 'inline';
        })
        .catch(Notification.exception);
};

// Delete template
const deleteTemplate = (tempid) => {
    unnotify();
    const link   = document.getElementById('quizchat-createlink');
    const form   = document.getElementById('quizchat-form');
    Str.get_strings([
        {key: 'confirm', component: 'moodle'},
        {key: 'areyousure', component: 'moodle'},
        {key: 'delete', component: 'moodle'},
        {key: 'cancel', component: 'moodle'}
    ]).done(function(strings) {
        Notification.confirm(
            strings[0], // Confirm.
            strings[1], // Are you sure?
            strings[2], // Delete.
            strings[3], // Cancel.
            function() {
                var promise = Ajax.call([{
                    methodname: 'block_quizchat_delete_template_message',
                    args: {
                        templateid: parseInt(tempid)
                    }
                }]);
                promise[0].then(function() {
                    refreshTable(true);
                    resetForm();
                    form.style.display = 'none';
                    link.style.display = 'inline';
                    return;
                }).fail(Notification.exception);
            }
        );
    }).fail(Notification.exception);
};

const txt_oninput = (oninput_event) => {
    // Prevent default first
    oninput_event.preventDefault();
    checkMsgLength(msglen , '#quizchat-message', '#charCount_msg');
    oninput_event.target.setCustomValidity('');
};

const txt_onblur = (onblur_event) => {
    // Prevent default first
    onblur_event.preventDefault();
    //if white space message
    if (checkWhiteSpaceMsg('#quizchat-message'))
    {
        updateCharsCount(msglen, '#charCount_msg');
    }
    onblur_event.target.setCustomValidity('');
};

const title_oninput = (oninput_event) => {
    // Prevent default first
    oninput_event.preventDefault();
    checkMsgLength(titlelen , '#quizchat-title', '#charCount_title');
    oninput_event.target.setCustomValidity('');
};

const title_onblur = (onblur_event) => {
    // Prevent default first
    onblur_event.preventDefault();
    //if white space message
    if (checkWhiteSpaceMsg('#quizchat-title'))
    {
        updateCharsCount(titlelen, '#charCount_title');
    }
    onblur_event.target.setCustomValidity('');
};

const initQuiztempmsgsDiv = () => {
    const link   = document.getElementById('quizchat-createlink');
    const form   = document.getElementById('quizchat-form');
    const cancel = document.getElementById('quizchat-cancel');
    const save   = document.getElementById('quizchat-save');
    const tableBody = document.querySelector('#quizchat-temp-msgs .generaltable tbody');
    const updateBtn   = document.getElementById('quizchat-update');
    const msg = document.getElementById('quizchat-message');
    const title = document.getElementById('quizchat-title');
    if (link && form) {
        msglen = msg.dataset.msglen;
        titlelen = title.dataset.titlelen;
        if (!link.dataset.bound) {
            link.dataset.bound = "1";
            link.addEventListener('click', function(e) {
                e.preventDefault();
                // remove existing notification inside region-main div, if any
                unnotify();
                link.style.display = 'none';
                updateBtn.style.display = 'none';
                save.style.display = 'inline-block';
                form.style.display = 'block';
            });
        }
        if (!cancel.dataset.bound) {
            cancel.dataset.bound = "1";
            cancel.addEventListener('click', function(e) {
                e.preventDefault();
                resetForm(); // clear textboxes
                form.style.display = 'none';
                link.style.display = 'inline';
            });
        }
        if (!save.dataset.bound) {
            save.dataset.bound = "1";
            save.addEventListener('click', function(e) {
                e.preventDefault();
                saveForm();
            });
        }
        if (!updateBtn.dataset.bound) {
            updateBtn.dataset.bound = "1";
            updateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                updateTemplate(updateBtn.dataset.id);
            });
        }
         // Attach click event to all edit buttons after rows are added
        const editButtons = tableBody.querySelectorAll('.edit_btn');
        editButtons.forEach(btn => {
            if (!btn.dataset.bound) {
                btn.dataset.bound = "1";
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const id = btn.dataset.id; // get data-id from link
                    editTemplate(id);
                });
            }
        });
        // Attach click event to all delete buttons after rows are added
        const deleteButtons = tableBody.querySelectorAll('.delete_btn');
        deleteButtons.forEach(btn => {
            if (!btn.dataset.bound) {
                btn.dataset.bound = "1";
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const tempid = btn.dataset.id; // get data-id from link
                    deleteTemplate(tempid);
                });
            }
        });
        $('#quizchat-title')
            .off('input.titleHandler blur.titleHandler')
            .on('input.titleHandler', title_oninput)
            .on('blur.titleHandler', title_onblur);
        $('#quizchat-message')
            .off('input.msgHandler blur.msgHandler')
            .on('input.msgHandler', txt_oninput)
            .on('blur.msgHandler', txt_onblur);
    }
};

const observer = new MutationObserver(() => {
    const form = document.getElementById('quizchat-form');
    if (form) {
        // Stop observing once form appears
        observer.disconnect();
        init();
    }
});
observer.observe(document.body, { childList: true, subtree: true });