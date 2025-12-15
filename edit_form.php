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
        /* $minutes = $mform->createElement(
            'text',
            'config_openbefore',
            '',
            ['size' => 5]// optional size.
        ); */

        // Types & defaults.
        //$mform->addRule('config_openbefore[number]', 'must be positive', 'positiveint', null, 'client');
       // $mform->setDefault('config_openbefore[timeunit]', 86400);
        //$mform->setType('config_openbefore', PARAM_INT);
        $mform->hideIf('config_openbefore[timeunit]', 'config_openbefore[timeunit]','eq', '1');
        $mform->setDefault('config_enableopenbefore', 0); // unchecked by default.
        //$mform->setDefault('config_openbefore', 20);
        $mform->setDefault('config_openbefore', 20 * MINSECS);

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
