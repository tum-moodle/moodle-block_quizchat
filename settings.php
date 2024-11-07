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

require_once(__DIR__ .'/lib/lib.php');

if ($ADMIN->fulltree) {
    $msg_length_setting = new admin_setting_configtext('block_quizchat/quizchat_msg_length',
            get_string('plugin_settings_msg_length_name', 'block_quizchat'),
            get_string('plugin_settings_msg_length_desc', 'block_quizchat'), 1000, PARAM_INT, 5);//5 is the configtext-size
    $poll_timeout_setting = new admin_setting_configtext('block_quizchat/quizchat_poll_timeout',
            get_string('plugin_settings_poll_interval_name', 'block_quizchat'),
            get_string('plugin_settings_poll_interval_desc', 'block_quizchat'), 10, PARAM_INT, 5);
    $unnotify_timeout_setting = new admin_setting_configtext('block_quizchat/unnotify_timeout',
            get_string('plugin_settings_unnotify_timeout_name', 'block_quizchat'),
            get_string('plugin_settings_unnotify_timeout_desc', 'block_quizchat'), 3, PARAM_INT, 5);
    // if values are not within the allowed range use minimus or maximums
    $poll_timeout_setting->set_updatedcallback(function() {
        if(QUIZCHAT_POLL_TIMEOUT_MIN > get_config('block_quizchat', 'quizchat_poll_timeout')){
            set_config('quizchat_poll_timeout', QUIZCHAT_POLL_TIMEOUT_MIN, 'block_quizchat');
            \core\notification::info(get_string('poll_timeout_setting_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_POLL_TIMEOUT_MAX < get_config('block_quizchat', 'quizchat_poll_timeout')){
            set_config('quizchat_poll_timeout', QUIZCHAT_POLL_TIMEOUT_MAX, 'block_quizchat');
            \core\notification::info(get_string('poll_timeout_setting_too_long', 'block_quizchat'));
        }
    });
    $unnotify_timeout_setting->set_updatedcallback(function() {
        if(QUIZCHAT_UNNOTIFY_TIMEOUT_MIN > get_config('block_quizchat', 'unnotify_timeout')){
            set_config('unnotify_timeout', QUIZCHAT_UNNOTIFY_TIMEOUT_MIN, 'block_quizchat');
            \core\notification::info(get_string('unnotify_timeout_setting_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_UNNOTIFY_TIMEOUT_MAX < get_config('block_quizchat', 'unnotify_timeout')){
            set_config('unnotify_timeout', QUIZCHAT_UNNOTIFY_TIMEOUT_MAX, 'block_quizchat');
            \core\notification::info(get_string('unnotify_timeout_setting_too_long', 'block_quizchat'));
        }
    });
    $msg_length_setting->set_updatedcallback(function() {
        if(QUIZCHAT_MSG_LENGTH_MIN > get_config('block_quizchat', 'quizchat_msg_length')){
            set_config('quizchat_msg_length', QUIZCHAT_MSG_LENGTH_MIN, 'block_quizchat');
            \core\notification::info(get_string('plugin_settings_msg_length_too_short', 'block_quizchat'));
        }
        if(QUIZCHAT_MSG_LENGTH_MAX < get_config('block_quizchat', 'quizchat_msg_length')){
            set_config('quizchat_msg_length', QUIZCHAT_MSG_LENGTH_MAX, 'block_quizchat');
            \core\notification::info(get_string('plugin_settings_msg_length_too_long', 'block_quizchat'));
        }
    });
    $settings->add($poll_timeout_setting);
    $settings->add($unnotify_timeout_setting);
    $settings->add($msg_length_setting);
}
