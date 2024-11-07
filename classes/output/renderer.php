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

use plugin_renderer_base;
use renderable;
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block timeline.
     *
     * @param main $main The main renderable
     * @return string HTML string
     */
    public function render_studentblock(studentblock $studentblock) {
        return $this->render_from_template('block_quizchat/studentblock', $studentblock->export_for_template($this));
    }
    public function render_qcmaster(qcmaster $master) {
        return $this->render_from_template('block_quizchat/qcmaster', $master->export_for_template($this));
    }
    public function render_instructorblock(instructorblock $instructorblock) {
        return $this->render_from_template('block_quizchat/instructorblock', $instructorblock->export_for_template($this));
    }
}
