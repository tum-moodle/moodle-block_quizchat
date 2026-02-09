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
class block_quizchat_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB,$PAGE;
        require_once(__DIR__ .'/lib/lib.php');
        $PAGE->requires->css('/blocks/quizchat/quizchat.css');
        $PAGE->requires->js_call_amd('block_quizchat/settings','init');
        //$PAGE->requires->js_amd_inline("require(['block_quizchat/settings'], function(M) {M.init();});");
        $PAGE->requires->js_call_amd('block_quizchat/chars_limit');
        // Section header title according to language file.
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
        //get block_quizchat-title config from DB
        $titletxt = $this->block->title;
        if($titletxt=='')
        {
            $titletxt = get_string('defaulttitle', 'block_quizchat');
        }
        $mform->addElement('text', 'config_title', get_string('blocktitle', 'block_quizchat'));
        $mform->setDefault('config_title', $titletxt);
        $mform->setType('config_title', PARAM_TEXT);
        // Define the checkbox.
        $enable = $mform->createElement(
            'advcheckbox',
            'config_enableopenbefore',
            '',
            get_string('enableopenbefore', 'block_quizchat')
        );

        // Define the minutes input.
        $minutes = $mform->createElement(
            'duration',
            'config_openbefore',
            '',
            ['optional' => false,'units' => [MINSECS], // only minutes
        'defaultunit' => MINSECS] // optional size.
        );
        
        $mform->hideIf('config_openbefore[timeunit]', 'config_openbefore[timeunit]','eq', '1');
        $mform->setDefault('config_enableopenbefore', 0); // unchecked by default.
        $mform->setDefault('config_openbefore', QUIZCHAT_OPEN_BEFORE_MINS * MINSECS);

        // Group: Checkbox + Minutes.
        $group = [];
        $group[] = $enable;
        $group[] = $minutes;

        $mform->addGroup(
            $group,
            'openbeforegroup',
            get_string('openbefore', 'block_quizchat'),
            ' ', // separator between elements in group
            false // no repeated elements.
        );

        

         

        // Help buttons.
        $mform->addHelpButton('openbeforegroup', 'openbefore', 'block_quizchat');

        // Disable minutes if checkbox not checked.
        $mform->disabledIf('config_openbefore', 'config_enableopenbefore', 'notchecked');

        // Load admin settings for block
        $blockconfig = get_config('block_quizchat');
        $quizchat = $DB->get_record('block_quizchat', array('instanceid' => $this->block->instance->id));
        $tempmenu_sitelevel = $blockconfig->enabletemplatemenu ?? 0;
        if ($tempmenu_sitelevel && $quizchat) {
            
            $mform->addElement('selectyesno', 'config_templatemsgsmenu', get_string('config_templatemsgsmenu', 'block_quizchat'));
            $mform->setDefault('config_templatemsgsmenu', 1);
            $mform->addElement('selectyesno', 'config_usecentraltempmsgs', get_string('config_usecentraltempmsgs', 'block_quizchat'));
            $mform->setDefault('config_usecentraltempmsgs', 1);
            // Disable minutes if checkbox not checked.
            $mform->disabledIf('config_usecentraltempmsgs', 'config_templatemsgsmenu', 'eq', 0);

            //template messages
            $mform->addElement('header', 'templateshdr', get_string('currenttemplatemessages', 'block_quizchat'));
            //$usecentraltempmsgs = $this->block->config->usecentraltempmsgs ?? ($this->block->config->templatemsgsmenu==="1"? true:false);
            $rows = get_template_messages('',null,false,intVal($quizchat->id), false); //get Quiz temp msgs (block)
            $rows6 = [];
            if (!empty($rows)) {
                // Keep only the first 6 columns of each row
                $rows6 = array_map(function($row) {
                    return array_slice($row, 0, 6);
                }, $rows);
            }
            //Quiz temp msgs
            $table = new html_table();
            $linkid   = 'quizchat-createlink';
            $formid   = 'quizchat-form';
            $titleid  = 'quizchat-title';
            $msgid    = 'quizchat-message';
            $checkboxid    = 'quizchat-ckbx_enabletemp';
            $saveid   = 'quizchat-save';
            $saveid_central = 'quizchat-save-central';
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
            $formhtml = html_writer::div('
                <div id="' . $formid . '" style="display:none; margin-top:10px;">
                    <input id="' . $titleid .'" name="' . $titleid .'" data-titlelen="'.QUIZCHAT_TEMP_MSG_LENGTH_MAX.'" type="text" placeholder="' . get_string('placeholder_title_setting', 'block_quizchat') . '" class="form-control mb-2" style="width:50%" pattern=".*\S.*" title="' . get_string('txtinput_required', 'block_quizchat') . '"/>
                    <small><span id="charCount_title" style="display:block; margin-bottom:10px;">' . QUIZCHAT_TEMP_MSG_LENGTH_MAX . get_string('spantxt_charCount', 'block_quizchat') . '</span></small>
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
            <div id="' . $emptytempid . '" style="margin-top: -10px; margin-left: -20px;'.(empty($rows)?' display:block;':' display:none;').'"> '. get_string('empty_temp_menu', 'block_quizchat') 
            .'</div>');
            $tablehtml .= html_writer::empty_tag('br');
            $tablehtml .= html_writer::link(
                '#', 
                get_string('createtemplatemessage', 'block_quizchat'), 
                ['id' => $linkid, 'name' => $linkid, 'style' =>'margin-left: -20px;']
            );
            $tablehtml .= $formhtml;
            //central temp msgs
            $table_central = new html_table();
            $emptytempid_central = 'empty_temp_central';
            $table_central->head  = array(
                get_string('table_action_settings', 'block_quizchat'),
                get_string('placeholder_title_setting', 'block_quizchat'), 
                get_string('placeholder_message_setting', 'block_quizchat'), 
                get_string('table_availability_settings', 'block_quizchat'), 
                get_string('table_createdby_settings', 'block_quizchat'), 
                get_string('table_moddate_settings', 'block_quizchat')
            );
            $table_central->size  = array('10%', '10%', '40%', '10%','15%','15%');
            $table_central->align = array('center', 'left', 'left', 'center', 'center', 'center');
            $table_central->attributes['style'] = 'width: 100% !important;  display:block;';
            $tablehtml_central = html_writer::table($table_central);
            $tablehtml_central .= html_writer::div('
            <div id="' . $emptytempid_central . '" style="margin-top: -10px; margin-left: -20px; display:none;"> '. get_string('empty_temp_menu', 'block_quizchat') 
            .'</div>');
            $tablehtml_central .= html_writer::empty_tag('br');
            // add both temp msgs typs in a menu
            $menuid   = 'quizchat-central-quiz-menu';
            $menu = html_writer::div('<div id="' . $menuid . '" style="display:block;">
            <div id="template-save-notice" class="alert alert-success" style="display:none;!important">
        ' . get_string('template_saved', 'block_quizchat') . '
    </div>
            <nav class="tab-links">
  <a data-target="quiztempmsgs" class="tab-link active" href="#">' . get_string('quiztempmsgs', 'block_quizchat') . '</a>
  <a data-target="centraltempmsgs" class="tab-link" href="#">' . get_string('centraltempmsgs', 'block_quizchat') . '</a>
</nav>

<div id="quiztempmsgs" class="tab-content active">'.$tablehtml.'</div>
<div id="centraltempmsgs" class="tab-content">'.$tablehtml_central.'<button id="' . $saveid_central . '" class="btn btn-primary">' . get_string('save_btn_settings', 'block_quizchat') . '</button></div></div>');
            $html = html_writer::tag('div', $menu, array(
                'id' => 'quizchat-temp-msgs',
                'name' => 'quizchat-temp-msgs-edit-form',
                'data-qcid' => $quizchat->id,
                'class' => 'form-setting',
                'style' =>'margin-right: -12px;'));
            $html .= html_writer::end_tag('div');
            $htmltag = html_writer::tag('div', $html, array('class' => 'form-item row', 'id' => 'templates-table-div', 'style' => 'padding-left:20px; padding-right:20px;'));
            $htmltag .= html_writer::end_tag('div');
            $htmltag .= html_writer::empty_tag('br');
            //$htmltag .= html_writer::script('require(["block_quizchat/settings"], function(M) { M.init(); });');
            
            $span = html_writer::tag('span', get_string('templatemessagesettings_desc', 'block_quizchat'), array(
                'class' => 'form-shortname d-block small text-muted',
                'style' => 'margin-left: -12px; padding-left:40px; padding-bottom: 25px;'));
            
            $mform->addElement('html', $span . $htmltag);
        }
    }

     /**
     * Display the configuration form when block is being added to the page
     *
     * @return bool
     */
    public static function display_form_when_adding(): bool {
        return true;
    }

}
