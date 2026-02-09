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
defined('MOODLE_INTERNAL') || die;
global $PAGE;

require_once(__DIR__ .'/lib/lib.php');

if ($ADMIN->fulltree) {
    $msg_length_setting = new admin_setting_configtext('block_quizchat/quizchat_msg_length',
            get_string('plugin_settings_msg_length_name', 'block_quizchat'),
            get_string('plugin_settings_msg_length_desc', 'block_quizchat'), 1000, PARAM_INT, 5);//5 is the configtext-size
    $poll_timeout_setting = new admin_setting_configtext('block_quizchat/quizchat_poll_timeout',
            get_string('plugin_settings_poll_interval_name', 'block_quizchat'),
            get_string('plugin_settings_poll_interval_desc', 'block_quizchat'), 10, PARAM_INT, 5);
    $unnotify_timeout_setting = new admin_setting_configtext('block_quizchat/unnotify_timeout',
            get_string('plugin_settings_unnotify_timeout_name', 'block_quizchat'),
            get_string('plugin_settings_unnotify_timeout_desc', 'block_quizchat'), 3, PARAM_INT, 5);
    // if values are not within the allowed range use minimus or maximums
    $poll_timeout_setting->set_updatedcallback(function() {
        if(QUIZCHAT_POLL_TIMEOUT_MIN > get_config('block_quizchat', 'quizchat_poll_timeout')){
            set_config('quizchat_poll_timeout', QUIZCHAT_POLL_TIMEOUT_MIN, 'block_quizchat');
            \core\notification::info(get_string('poll_timeout_setting_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_POLL_TIMEOUT_MAX < get_config('block_quizchat', 'quizchat_poll_timeout')){
            set_config('quizchat_poll_timeout', QUIZCHAT_POLL_TIMEOUT_MAX, 'block_quizchat');
            \core\notification::info(get_string('poll_timeout_setting_too_long', 'block_quizchat'));
        }
    });
    $unnotify_timeout_setting->set_updatedcallback(function() {
        if(QUIZCHAT_UNNOTIFY_TIMEOUT_MIN > get_config('block_quizchat', 'unnotify_timeout')){
            set_config('unnotify_timeout', QUIZCHAT_UNNOTIFY_TIMEOUT_MIN, 'block_quizchat');
            \core\notification::info(get_string('unnotify_timeout_setting_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_UNNOTIFY_TIMEOUT_MAX < get_config('block_quizchat', 'unnotify_timeout')){
            set_config('unnotify_timeout', QUIZCHAT_UNNOTIFY_TIMEOUT_MAX, 'block_quizchat');
            \core\notification::info(get_string('unnotify_timeout_setting_too_long', 'block_quizchat'));
        }
    });
    $msg_length_setting->set_updatedcallback(function() {
        if(QUIZCHAT_MSG_LENGTH_MIN > get_config('block_quizchat', 'quizchat_msg_length')){
            set_config('quizchat_msg_length', QUIZCHAT_MSG_LENGTH_MIN, 'block_quizchat');
            \core\notification::info(get_string('plugin_settings_msg_length_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_MSG_LENGTH_MAX < get_config('block_quizchat', 'quizchat_msg_length')){
            set_config('quizchat_msg_length', QUIZCHAT_MSG_LENGTH_MAX, 'block_quizchat');
            \core\notification::info(get_string('plugin_settings_msg_length_too_long', 'block_quizchat'));
        }
    });
    $settings->add($poll_timeout_setting);
    $settings->add($unnotify_timeout_setting);
    $settings->add($msg_length_setting);
}

if ($ADMIN->fulltree && $PAGE->pagetype=="admin-setting-blocksettingquizchat") {
    $PAGE->requires->js_call_amd('block_quizchat/settings','init');
    $PAGE->requires->js_call_amd('block_quizchat/chars_limit');
    $PAGE->requires->css('/blocks/quizchat/quizchat.css');
    //template messages
    $table = new html_table();
    $rows = get_template_messages('', null,null, null, null);
    $rows6 = [];
    if (!empty($rows)) {
        // Keep only the first 6 columns of each row
        $rows6 = array_map(function($row) {
            return array_slice($row, 0, 6);
        }, $rows);
    }
    $linkid   = 'quizchat-createlink';
    $formid   = 'quizchat-form';
    $titleid  = 'quizchat-title';
    $msgid    = 'quizchat-message';
    $checkboxid    = 'quizchat-ckbx_enabletemp';
    $saveid   = 'quizchat-save';
    $cancelid = 'quizchat-cancel';
    $updateid   = 'quizchat-update';
    $emptytempid = 'empty_temp';
    $table->head  = array(
                            get_string('table_action_settings', 'block_quizchat'),
                            get_string('placeholder_title_setting', 'block_quizchat'), 
                            get_string('placeholder_message_setting', 'block_quizchat'), 
                            get_string('table_availability_settings', 'block_quizchat'), 
                            get_string('table_createdby_settings', 'block_quizchat'), 
                            get_string('table_moddate_settings', 'block_quizchat')
                        );
    $table->size  = array('10%', '10%', '40%', '10%','15%','15%');
    $table->align = array('center', 'left', 'left', 'center', 'center', 'center');
    $table->attributes['style'] = 'width: 100% !important;' .(empty($rows)?' display:none;':' display:block;');
    if(!empty($rows)) $table->data  = $rows6;
    $html = html_writer::tag('div', get_string('currenttemplatemessages', 'block_quizchat'), array(
        'class' => 'form-label col-sm-3 text-sm-end',
        'style' => 'margin-left: -12px;'));
    
    $formhtml = html_writer::div('
        <div id="' . $formid . '" style="display:none; margin-top:10px;">
            <input id="' . $titleid . '" name="' . $titleid . '" data-titlelen="'.QUIZCHAT_TEMP_MSG_LENGTH_MAX.'" type="text" placeholder="' . get_string('placeholder_title_setting', 'block_quizchat') . '" class="form-control mb-2" style="width:50%" pattern=".*\S.*" title="' . get_string('txtinput_required', 'block_quizchat') . '"/>
             <small><span id="charCount_title" style="display:block; margin-bottom:10px;">'.QUIZCHAT_TEMP_MSG_LENGTH_MAX.get_string('spantxt_charCount', 'block_quizchat').'</span></small>
            <textarea id="' . $msgid . '" name="' . $msgid . '" data-msglen="'.get_config('block_quizchat', 'quizchat_msg_length').'" placeholder="' . get_string('placeholder_message_setting', 'block_quizchat') . '" class="form-control mb-2" style="width:50%" pattern=".*\S.*" title="' . get_string('txtinput_required', 'block_quizchat') . '"></textarea>
        <small><span id="charCount_msg" style="display:block; margin-bottom:10px;">'.get_config('block_quizchat', 'quizchat_msg_length').get_string('spantxt_charCount', 'block_quizchat').'</span></small>
            <div class="form-check mb-3" style="width:50%; text-align:left;">
                <input type="checkbox" id="' . $checkboxid . '" class="form-check-input" checked>
                <label for="' . $checkboxid . '" class="form-check-label">'
                    . get_string('enabletemplate_sitelevel', 'block_quizchat') .
                '</label>
            </div>
        
            <button id="' . $saveid . '" name="' . $saveid . '" class="btn btn-primary">' . get_string('save_btn_settings', 'block_quizchat') . '</button>
            <button id="' . $updateid . '" name="' . $updateid . '" class="btn btn-primary" style="display:none;">' . get_string('update_btn_settings', 'block_quizchat') . '</button>
            <button id="' . $cancelid . '" name="' . $cancelid . '" class="btn btn-secondary">' . get_string('cancel_btn_settings', 'block_quizchat') . '</button>
        </div>
    ');
    
    $tablehtml = html_writer::table($table);
    $tablehtml .= html_writer::div('
            <div id="' . $emptytempid . '" style="margin-top: -10px;'.(empty($rows)?' display:block;':' display:none;').'"> '. get_string('empty_temp_menu', 'block_quizchat') 
            .'</div>');
    $tablehtml .= html_writer::empty_tag('br');
    $tablehtml .= html_writer::link(
        '#', 
        get_string('createtemplatemessage', 'block_quizchat'), 
        ['id' => $linkid, 'name' => $linkid]
    );
    $tablehtml .= $formhtml;
    $html .= html_writer::tag('div', $tablehtml, array(
        'id' => 'quizchat-temp-msgs',
        'name' => 'quizchat-temp-msgs-settings',
        'data-qcid' => '0',
        'class' => 'form-setting col-sm-9',
        'style' =>'margin-right: -12px;'));
    $html .= html_writer::end_tag('div');
    $htmltag = html_writer::tag('div', $html, array('class' => 'form-item row'));
    $htmltag .= html_writer::end_tag('div');
    $htmltag .= html_writer::empty_tag('br');
    
    $span = html_writer::tag('span', get_string('templatemessagesettings_desc', 'block_quizchat'), array(
        'class' => 'form-shortname d-block small text-muted',
        'style' => 'margin-left: -12px;'));
    $settings->add(new admin_setting_heading(
        'block_quizchat/tableheading',
        get_string('templatemessagesettings', 'block_quizchat'),
        $span . $htmltag
    ));

    // Checkbox setting to enable/disable the template menu in the block.
    $settings->add(new admin_setting_configcheckbox(
        'block_quizchat/enabletemplatemenu', 
        get_string('enabletemplatemenu', 'block_quizchat'),
        get_string('enabletemplatemenu_desc', 'block_quizchat'),
        0  // Default value (0 = unchecked, 1 = checked)
    ));
}
