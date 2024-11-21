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
namespace block_quizchat\event;

defined('MOODLE_INTERNAL') || die();

class message_sent extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'c'; // Create
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'block_quizchat_messages';
    }

    public static function get_name() {
        return get_string('eventmessageadded', 'block_quizchat');
    }

    public function get_description() {
        return get_string('eventmessageadded', 'block_quizchat');
    }

    public function get_url() {
        return new \moodle_url('/blocks/block_quizchat/view.php', ['blockid' => $this->blockinstanceid, 'cmid' =>$this->cmid]);
    }
}
