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

$functions = array(
    // The name of your web service function, as discussed above.
    'block_quizchat_get_messages' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\get_messages',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get messages addressed to user or to all users.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
    ],
    'block_quizchat_create_message' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\create_message',

        // A brief, human-readable, description of the web service function.
        'description' => 'Post a new message to the quizchat.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

    ],
    'block_quizchat_get_users' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\get_users',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get Quiz participants by quizid',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

    ],
    'block_quizchat_get_questionslot' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\get_questionslot',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get question slot order by senderid and quizchatid. ',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

    ],
    'block_quizchat_create_template_message' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\create_template_message',

        // A brief, human-readable, description of the web service function.
        'description' => 'Add a new template message.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

    ],
    'block_quizchat_get_template_messages' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\get_template_messages',

        // A brief, human-readable, description of the web service function.
        'description' => 'Get template messages.',

        // Options include read, and write.
        'type'        => 'read',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
    ],
    'block_quizchat_delete_template_message' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\delete_template_message',

        // A brief, human-readable, description of the web service function.
        'description' => 'Delete template message.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

        // An optional list of services where the function will be included.
    ],
    'block_quizchat_exclude_central_template_messages' => [
        // The name of the namespaced class that the function is located in.
        'classname'   => 'block_quizchat\external\exclude_central_template_messages',

        // A brief, human-readable, description of the web service function.
        'description' => 'Exclude or add a central template message to a block.',

        // Options include read, and write.
        'type'        => 'write',

        // Whether the service is available for use in AJAX calls from the web.
        'ajax'        => true,

    ]
);
