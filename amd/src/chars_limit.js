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
 * JavaScript library for the block_quizchat plugin.
 *
 * @package
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
let targetSpan = $('#charCount').length ? $('#charCount') : $('#charCount_msg');
var spaceIndex = targetSpan.text().indexOf(' ');
var spanTxtCharCount = targetSpan.text().substring(spaceIndex + 1);

export const resetCharsCount = (maxLen, counterSpanSel = null) => {
    const target = counterSpanSel ?? '#charCount';
    $(target).text(maxLen + ' ' + spanTxtCharCount);
};

export const checkCharsLength = (maxLen , inputId, counterSpanSel = null) => {
    const target = counterSpanSel ?? '#charCount';
    var currentLen = $(inputId).val().length;
    var remainChars = maxLen - currentLen;
    // Stop adding more characters if limit exceeded
    if (remainChars < 0) {
      $(inputId).val($(inputId).val().substring(0, maxLen));
      remainChars = 0;
    }
    // Reset the appearance of the message
    $(target).text(remainChars + ' ' + spanTxtCharCount);
};

export const handleWhiteSpaceMsg = (inputTextName) => {
  var spacesMsg = false;
  //if white space message
  if ($(inputTextName).val().replace(/&nbsp;?|&#160;?/g, ' ').trim() === '')
  {
      $(inputTextName).val('');
      spacesMsg = true;
  }
  return spacesMsg;
};
