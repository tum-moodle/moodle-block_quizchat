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
namespace block_quizchat\external;

use external_function_parameters;
use external_single_structure;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/../../lib/lib.php');

class get_questionslot extends \external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new \external_function_parameters([
            'senderid'          => new \external_value(PARAM_INT, 'Message sender id'),
            'quizchatid'     => new \external_value(PARAM_INT, 'The quizchat id'),
            'partial_name' => new \external_value(PARAM_RAW, 'Partial firstname or lastname of participant.'),
            'general_string' => new \external_value(PARAM_RAW, 'Language string general')
        ]);
    }
    public static function execute_returns() {
        return new \external_single_structure([
            'questions' => new \external_multiple_structure(
                new external_single_structure([
                'questionid' => new \external_value(PARAM_INT, 'Question id'),
                'teacherslotorder' => new \external_value(PARAM_INT, 'The slot order of a question in a quiz as added by teacher'),
                'questionsummary' => new \external_value(PARAM_RAW, 'Question summary'),
                'studentquestionorder' => new \external_value(PARAM_INT, 'student question order'),
                'questionlink' => new \external_value(PARAM_RAW, 'Question preview link'),
                'questionattemptid' => new \external_value(PARAM_INT, 'Question attempt id'),
                'questionname' => new \external_value(PARAM_RAW, 'Question name'),
            ]), 'One message data set', VALUE_OPTIONAL
            )
        ]);
    }
    public static function execute($senderid, $quizchatid, $partial_name, $general_string) {
        $questions_infos = [];
        $partial_name_sql = preg_replace('/\W/i', ' ', $partial_name);//replaces all non-word characters (including underscores) with a space.
        $partial_name_sql = preg_replace('/\s+/i', ' ', $partial_name_sql);//replaces all occurrences of one or more whitespace characters with a single space.
        $questions_infos = get_slotorder($senderid, $quizchatid, $partial_name, $general_string, null);
        return $questions_infos;
    }
}
