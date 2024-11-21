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
defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_quiz\event\attempt_deleted',
        'callback' => '\block_quizchat\quizchat_observers::quiz_attempt_deleted',
        'internal'  => true,
        'priority'    => 9999,
    ],
    [
        'eventname' => '\block_quizchat\event\message_sent',
        'callback' => '\block_quizchat\quizchat_observers::message_sent',
        'internal'  => true,
        'priority'    => 9999,
    ],
];