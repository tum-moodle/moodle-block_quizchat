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
    global $DB;
    $table = new xmldb_table('block_quizchat');
    $dbman = $DB->get_manager();
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
        $dbman->create_table($table.'_messages');
        $dbman->create_table($table.'_group');
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

    return true;
}
