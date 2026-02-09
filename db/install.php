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


function xmldb_block_quizchat_install() {
    global $DB, $USER;
    $group1 = ['name' => 'all', 'msgid' => null, 'groupid' => null, 'groupingid' => null];
    $group2 = ['name' => 'teachers', 'msgid' => null, 'groupid' => null, 'groupingid' => null];
    $DB->insert_record('block_quizchat_group', $group1);
    $DB->insert_record('block_quizchat_group', $group2);
    $tempmsg1 = ['title' => 'Restroom Rules','template' => 'If you need to use the restroom, please request permission before leaving your seat.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $tempmsg2 = ['title' => 'Restroom Break Approved','template' => 'Your restroom break request is approved. Please leave all exam materials at your desk.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $tempmsg3 = ['title' => 'Restroom Break Pending','template' => 'You will need to wait a moment before taking a restroom break. Please remain seated.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $DB->insert_record('block_quizchat_templates', $tempmsg1);
    $DB->insert_record('block_quizchat_templates', $tempmsg2);
    $DB->insert_record('block_quizchat_templates', $tempmsg3);
}
