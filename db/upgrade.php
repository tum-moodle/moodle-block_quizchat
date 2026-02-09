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
function xmldb_block_quizchat_upgrade($oldversion) {
    global $DB, $USER;
    $table = new xmldb_table('block_quizchat');
    $dbman = $DB->get_manager();
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
        $dbman->create_table($table.'_messages');
        $dbman->create_table($table.'_group');
        $dbman->create_table($table.'_templates');
    }

    if($oldversion < 2025052003) {
        // Define the table where the column will be added
        $table_msgs = new xmldb_table('block_quizchat_messages');
        
        // Define the new field (column) to be added, setting a default value
        $field_mgroup = new xmldb_field('mgroupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        
        // Check if the field does not exist, then add it
        if (!$dbman->field_exists($table_msgs, $field_mgroup)) {
            // Add the new field (column) with a default value for existing rows
            $field_mgrouping = new xmldb_field('mgroupingid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $field_gname = new xmldb_field('gname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $dbman->add_field($table_msgs, $field_mgroup);
            $dbman->add_field($table_msgs, $field_mgrouping);
            $dbman->add_field($table_msgs, $field_gname);
        }
        // Save the upgrade progress in the Moodle database
        upgrade_plugin_savepoint(true, 2025052003,'block', 'quizchat');
    }

    $table_tmplts = new xmldb_table('block_quizchat_templates');
    if($oldversion < 2025092505 || !$dbman->table_exists($table_tmplts)) {
        // Define the templates table and adding fields to table block_quizchat_templates.
        $table_tmplts->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table_tmplts->add_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('template', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('type', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('isenabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('isquizlevel', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table_tmplts->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table_tmplts->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table_tmplts->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $dbman->create_table($table_tmplts);

        // changes in block_quizchat table
        $tempmenuenabled_field = new xmldb_field('tempmenuenabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if(!$dbman->field_exists($table, $tempmenuenabled_field)) {
            $dbman->add_field($table, $tempmenuenabled_field);
        }

        //create block_quizchat_block_templates table
        $table_blktmplts = new xmldb_table('block_quizchat_block_templates');
        if(!$dbman->table_exists($table_blktmplts)) {
            $table_blktmplts->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table_blktmplts->add_field('quizchatid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table_blktmplts->add_field('templateid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table_blktmplts->add_field('isexcluded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
            $table_blktmplts->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table_blktmplts->add_key('quizchatid', XMLDB_KEY_FOREIGN, ['quizchatid'], 'block_quizchat', ['id']);
            $table_blktmplts->add_key('templateid', XMLDB_KEY_FOREIGN, ['templateid'], 'block_quizchat_templates', ['id']);
            $dbman->create_table($table_blktmplts);
        }

        $tempmsg1 = ['title' => 'Restroom Rules','template' => 'If you need to use the restroom, please request permission before leaving your seat.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
        $tempmsg2 = ['title' => 'Restroom Break Approved','template' => 'Your restroom break request is approved. Please leave all exam materials at your desk.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
        $tempmsg3 = ['title' => 'Restroom Break Pending','template' => 'You will need to wait a moment before taking a restroom break. Please remain seated.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
        $DB->insert_record('block_quizchat_templates', $tempmsg1);
        $DB->insert_record('block_quizchat_templates', $tempmsg2);
        $DB->insert_record('block_quizchat_templates', $tempmsg3);
        
        // Save the upgrade progress in the Moodle database
        upgrade_plugin_savepoint(true, 2025092505,'block', 'quizchat');
    }
    /* $table_blktmplts = new xmldb_table('block_quizchat_block_templates');
    if($oldversion < 2025111742 || $dbman->table_exists($table_blktmplts)) {
        $isexcluded_field = new xmldb_field('isexcluded', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if(!$dbman->field_exists($table_blktmplts, $isexcluded_field)) {
            $dbman->add_field($table_blktmplts, $isexcluded_field);
        }
        // Save the upgrade progress in the Moodle database
        upgrade_plugin_savepoint(true, 2025111742,'block', 'quizchat');
    } */
   /* if($oldversion < 2025111817) {
    $tempmsg1 = ['title' => 'Restroom Rules','template' => 'If you need to use the restroom, please request permission before leaving your seat.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $tempmsg2 = ['title' => 'Restroom Break Approved','template' => 'Your restroom break request is approved. Please leave all exam materials at your desk.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $tempmsg3 = ['title' => 'Restroom Break Pending','template' => 'You will need to wait a moment before taking a restroom break. Please remain seated.', 'type' => 'msg', 'isenabled' => 1, 'isquizlevel' => 0, 'userid' => $USER->id, 'timecreated' => time(),  'timemodified' => time()];
    $DB->insert_record('block_quizchat_templates', $tempmsg1);
    $DB->insert_record('block_quizchat_templates', $tempmsg2);
    $DB->insert_record('block_quizchat_templates', $tempmsg3);
    upgrade_plugin_savepoint(true, 2025111817,'block', 'quizchat');
   } */
    return true;
}
