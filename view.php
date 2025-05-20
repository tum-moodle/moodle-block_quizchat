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
require_once('../../config.php');
require_once(__DIR__ .'/lib/lib.php');
global $DB;
//$blockid = required_param('blockid', PARAM_INT);
$cmid = required_param('id', PARAM_INT);
$cm = $DB->get_record('course_modules', ['id' => $cmid]);
$quizchat = $DB->get_record('block_quizchat', array('cmid' => $cmid));
$blockid = $quizchat->instanceid;
// Check permissions and context
$context = context_block::instance($blockid);
require_login();
require_capability('block/quizchat:sendall', $context);

// Retrieve block content
$blockinstance = $DB->get_record('block_instances', array('id' => $blockid), '*', MUST_EXIST);
$blockcontext = context_block::instance($blockid);
$theblock = block_instance($blockinstance->blockname, $blockinstance);
// Page setup
$PAGE->set_url('/blocks/quizchat/view.php', array('id' => $cmid));
$PAGE->set_context($context);
$PAGE->set_pagelayout('popup');
$PAGE->set_title($theblock->title);
$PAGE->set_heading($theblock->title);
$PAGE->set_cm($cm);
$content = $theblock->get_content();
echo $OUTPUT->header();
echo $OUTPUT->heading($theblock->title);
echo html_writer::empty_tag('br');
echo html_writer::div($content->text, 'block-content');
echo $OUTPUT->footer();
