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
namespace block_quizchat\output;
defined('MOODLE_INTERNAL') || die;
require_once("$CFG->libdir/formslib.php");

class tempmsgs_form extends \moodleform
{
    function definition()
    {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = $this -> _form;
        $mform -> disable_form_change_checker();
        $mform->updateAttributes(array('class' => 'mform_quizchat', 'id' => 'block_quizchat_template_msgs'));
        $q_info = $DB -> get_record('quiz', array('id' => $PAGE -> cm -> instance));
        
        // Add template messages menu if enabled in block and site level
        $blockconfig = get_config('block_quizchat');//site level
        $tempmenu_sitelevel = $blockconfig->enabletemplatemenu ?? 0;
        $enabletempmenu_blk = $this->_customdata['enabletempmenu'] ?? 0;
        if($tempmenu_sitelevel && $enabletempmenu_blk) {
            $tempoptions = array(  
                'multiple' => false,                                               
                'noselectionstring' => '',
                'ajax' => 'block_quizchat/tempmsg_select'                                                  
            );
            $tempselect = $mform->createElement('autocomplete', 'block_quizchat_temp_menu', get_string('block_quizchat_temp_menu', 'block_quizchat'), [], $tempoptions);
            $mform->addElement($tempselect);
            $usecentraltemps = $mform -> createElement('hidden', 'block_quizchat_usecentraltempmsgs', $this->_customdata['usecentraltempmsgs']);
            $mform->setType('block_quizchat_usecentraltempmsgs', PARAM_INT);
            $mform->addElement($usecentraltemps);
        }
    } 

}
