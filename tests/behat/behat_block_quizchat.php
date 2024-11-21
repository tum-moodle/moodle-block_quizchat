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
 * Steps definitions related to block_quizchat plugin.
 *
 * @package   block_quizchat
 * @copyright 2023, TUM ProLehre | Medien und Didaktik <moodle@tum.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

class behat_block_quizchat extends behat_base {

  /**
   * Checks, that the specified element contains the specified text (regardless of whether it is visible or not).
   *
   * @Then /^"(?P<element_string>(?:[^"]|\\")*)" "(?P<text_selector_string>[^"]*)" should contain "(?P<text_string>(?:[^"]|\\")*)" text$/
   * @throws ElementNotFoundException
   * @throws ExpectationException
   * @param string $text
   * @param string $element Element we look in.
   * @param string $selectortype The type of element where we are looking in.
   */
  public function assert_element_contains_text($element, $selectortype, $text) {

    // Getting the container where the text should be found.
    $container = $this->get_selected_node($selectortype, $element);

    // Looking for all the matching nodes without any other descendant matching the
    // same xpath (we are using contains(., ....).
    $xpathliteral = behat_context_helper::escape($text);
    $xpath = "/descendant-or-self::*[contains(., $xpathliteral)]" .
        "[count(descendant::*[contains(., $xpathliteral)]) = 0]";

    // Wait until it finds the text inside the container, otherwise custom exception.
    try {
        $nodes = $this->find_all('xpath', $xpath, false, $container);
    } catch (ElementNotFoundException $e) {
        throw new ExpectationException('"' . $text . '" text was not found in the "' . $element . '" element', $this->getSession());
    }
  }
  /**
     * Pause the test until return is pressed
     *
     *
     * @Given /^I pause the test until return is pressed$/
     */
  public function i_pause_the_test_until_return_is_pressed() {
    fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {}
        fwrite(STDOUT, "\033[u");
        return;
  }

  /**
   * Waits a while, for debugging.
   *
   * @param int $seconds
   *   How long to wait.
   *
   * @When /^I wait for (\d+) seconds$/
   */
  public function iWaitForSeconds($seconds) {
    sleep($seconds);
  }
  /**
     * Start a quiz attempt without answers with specific questions order.
     *
     * The supplied data table for have a row for each slot where you want
     * to force either which question was chose was used.
     *
     * @param string $username the username of the user that will attempt.
     * @param string $quizname the name of the quiz the user will attempt.
     * @param string $layout information about the questions to add, as above.
     * @Given /^user "([^"]*)" has started an attempt at quiz "([^"]*)" with layout "([^"]*)"$/
     */
    public function user_has_started_an_attempt_at_quiz_with_layout($username, $quizname, $layout) {
      global $DB;

      $quizid = $DB->get_field('quiz', 'id', ['name' => $quizname], MUST_EXIST);
      $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
      $sql_lastattempt = "SELECT *
      FROM {quiz_attempts}
      WHERE quiz = ".$quizid."
      AND userid = ".$user->id."
      ORDER BY timestart DESC
      LIMIT 1;";
      $lastattempt = $DB->get_record_sql($sql_lastattempt);
      $record = new stdclass;
      $record->id                         =      $lastattempt->id;
      $record->quiz                       =      $lastattempt->quiz;
      $record->userid                     =      $lastattempt->userid;
      $record->attempt                    =      $lastattempt->attempt;
      $record->uniqueid                   =      $lastattempt->uniqueid;
      $record->layout                     =      $layout;
      $record->currentpage                =      $lastattempt->currentpage;
      $record->preview                    =      $lastattempt->preview;
      $record->state                      =      $lastattempt->state;
      $record->timestart                  =      $lastattempt->timestart;
      $record->timefinish                 =      $lastattempt->timefinish;
      $record->timemodified               =      $lastattempt->timemodified;
      $record->timemodifiedoffline        =      $lastattempt->timemodifiedoffline;
      $record->timecheckstate             =      $lastattempt->timecheckstate;
      $record->sumgrades                  =      $lastattempt->sumgrades;
      $record->gradednotificationsenttime =      $lastattempt->gradednotificationsenttime;
      
      $sql = $DB->update_record('quiz_attempts', $record); 
}

  /**
     * Send message in the last created Quizchat block.
     *
     * @param string $message the Quizchat message.
     * @param string $senderusername the username of the sender.
     * @param string $receiverusername the username of the receiver (private) or the name of the receiving group (all or teachers).
     * @param string $questionname the name of the question (if exist or general) that should be referenced in the message.
     * @param string $quizname the name of Quizchat quiz.
     * @Given /^A Quizchat message "([^"]*)" is sent from "([^"]*)" to "([^"]*)" about question "([^"]*)" at quiz "([^"]*)"$/
     */
    public function a_quizchat_message_is_sent_from_to_about_question_at_quiz($message, $senderusername, $receiverusername, $questionname, $quizname) {
      global $DB;
      require_once(__DIR__ . '/../../lib/lib.php');

      $quizid = $DB->get_field('quiz', 'id', ['name' => $quizname], MUST_EXIST);
      $qchat = $DB->get_record('block_quizchat', ['quiz' => $quizid], '*', MUST_EXIST);
      if($qchat) {
      $senderid = $DB->get_field('user', 'id', ['username' => $senderusername], MUST_EXIST);
      $receiverid = -1;
      $groupid = -1;
      $questionid = -1;
      if(strcmp($questionname, 'general') == 0) {//general question
        if(strcmp($receiverusername, 'all') == 0 || strcmp($receiverusername, 'teachers') == 0) {
          $receiverid = 0;
          $groupid = $DB->get_field('block_quizchat_group', 'id', ['name' => $receiverusername], MUST_EXIST);
        } else {
          $receiverid = $DB->get_field('user', 'id', ['username' => $receiverusername], MUST_EXIST);
          $groupid = 0;
        }
        $questionid = null;
      } else {// a question is selected
        if (strcmp($receiverusername, 'teachers') == 0) {// to teachers
          $receiverid = 0;
          $groupid = $DB->get_field('block_quizchat_group', 'id', ['name' => $receiverusername], MUST_EXIST);
        } else if (strcmp($receiverusername, 'group') == 0) { // to question group
          $receiverid = 0;
          $groupid = 0;
        }
        else {// to user
          $receiverid = $DB->get_field('user', 'id', ['username' => $receiverusername]);
          if(!$receiverid) {
            $receiverid = 0;
          }
          $groupid = 0;
        }
        $questionid = $DB->get_field('question', 'id', ['name' => $questionname], MUST_EXIST);
      }
      $questionattemptid = null;
      $sender_hascap = check_sendallcap($qchat, $senderid);
      if(!$sender_hascap && !is_null($questionid) ) {
        //implelment student reference a question
        $attempt_query = "SELECT ques_a.id as questionattemptid, last_qa.attempt , ques_a.questionid, ques_a.slot, last_qa.layout
        FROM {question_attempts} as ques_a
        join (SELECT *
                FROM {quiz_attempts}
                WHERE userid = " .$senderid. "
                AND quiz = ".$qchat->quiz."
                ORDER BY timestart DESC
                LIMIT 1) as last_qa 
        on last_qa.uniqueid = ques_a.questionusageid
        where ques_a.questionid = ".$questionid.";";
        $question_attempt_record = $DB->get_record_sql($attempt_query);
        $questionattemptid = $question_attempt_record->questionattemptid;
        $questionid = null;
      }

      return create_msg($qchat->id, $receiverid, $message, $groupid, $questionattemptid, $questionid, $senderid);
    }
    else {
      return -1;
    }
}

/**
 * Waits a while, for an element to be visible.
 *
 * @param int    $timeout_seconds The maximum timeout in seconds (2 digits max)
 * @param string $selector        The CSS selector to wait for.
 *
 * @When /^I wait for element with selector "([^"]*)"$/
 * @When /^I wait "([0-9]{1,2})" seconds for element with selector "([^"]*)" to become visible$/
 */
public function iWaitForElementVisibility($timeout_seconds, $selector)
{
    $this->spin(
      function () use ($selector) {
          $element = $this->getSession()->getPage()->find('css', $selector);
          if ($element === null) {
              return false;
          }
          return $element;
      },
      [],
      (int) $timeout_seconds,  // Timeout in seconds
      new Exception("Element with selector '$selector' cannot be found or is not visible."), 
      1000000  // Microsleep in microseconds (1 second)
  );
}

    /**
     * Change the timestamp of the last quizchat message
     *
     * @Given /^the last quizchat message date is set to "(?P<days_number>\d+)" days back$/
     * @param int $no_of_days
     */
    public function last_quizchatmsg_date_is_set_to_days_back($no_of_days) {
      global $DB;

      $day_in_seconds = 86400;//24*60*60
      // Get the last message
      $sql_lastmsg = "SELECT *
      FROM {block_quizchat_messages}
      ORDER BY timestamp DESC
      LIMIT 1;";
      $lastmsg = $DB->get_record_sql($sql_lastmsg);
      // Subtract no_of_days from the timestamp of the last message
      $timestamp = $lastmsg->timestamp - ($no_of_days * $day_in_seconds);
      // Update the timestamp for the record.
      $DB->set_field('block_quizchat_messages', 'timestamp', $timestamp, ['id' => $lastmsg->id]);
  }

  /**
     * see if the date of no of days back in a CSS selector 
     *
     * @Given /^I should see "(?P<days_number>\d+)" days back in the selector "([^"]*)"$/
     * @param int $no_of_days
     * @param string $selector        The CSS selector
     */
  public function i_should_see_days_back_in_selector($no_of_days, $selector)
  {
      $today = new DateTime();
      // Subtract the specified number of days to get the target date
      $interval = new DateInterval("P{$no_of_days}D");
      $date = $today->sub($interval);
      
      // formated date
      $fdatetime = userdate($date->getTimestamp(), get_string('strftimerecentfull', 'langconfig'));
      $fdate = implode(',', array_slice(explode(',', $fdatetime), 0, 2));
      
      // Find the element using the CSS selector
      $element = $this->getSession()->getPage()->find('css', $selector);

      // Check if the element exists
      if (!$element) {
          throw new ElementNotFoundException($this->getSession()->getDriver(), null, 'css', $selector);
      }

      // Get the text content of the element
      $elementText = $element->getText();

      // Check if the formatted date is in the element's text
      if (strpos($elementText, $fdate) === false) {
          throw new Exception("The date '$fdate' was not found in the element with the selector '$selector'. Actual text was: '$elementText'.");
      }
  }

/**
 * Copying the last quizchat message a given number of times.
 *
 * @Given /^I copy the last quizchat message "(?P<repeats>\d+)" times$/
 * @param int $repeates Number of times to copy the last quizchat message.
 */

public function i_copy_last_quizchatmsg_times($repeates) {
    global $DB;
    // Get the last message
    $sql_lastmsg = "SELECT *
    FROM {block_quizchat_messages}
    ORDER BY timestamp DESC
    LIMIT 1;";
    $lastmsg = $DB->get_record_sql($sql_lastmsg);
    if ($lastmsg) {
      for ($i = 0; $i < $repeates; $i++) {
          $newmsg = array(
              'quizchatid' => $lastmsg->quizchatid,
              'userid'     => $lastmsg->userid,
              'receiverid' => $lastmsg->receiverid,
              'groupid'    => $lastmsg->groupid,
              'message'    => $lastmsg->message,
              'timestamp'  => $lastmsg->timestamp,
              'questionattemptid' => $lastmsg->questionattemptid,
              'questionid' => $lastmsg->questionid
          );

          $DB->insert_record('block_quizchat_messages', $newmsg);
      }
    } else {
      throw new Exception("No messages found to copy.");
    }
  }

  /**
 * Check whether the count of date class is equal to the inserted number
 *
 * @Given /^The count of date classes should be "(?P<count>\d+)"$/
 * @param int $count The count of date class
 */

public function check_count_date($count) {
  // Find the elements using the CSS selector
  $date_elements =  $this->getSession()->getPage()->findAll('css', '.line-with-text');
  if(!empty($date_elements))
  {
    if(count($date_elements) != $count)
    {
      throw new Exception("The count of date classes doesn't match the input number. The actual count is ".count($date_elements));
    }
  }
  else {
    throw new Exception("No dates found.");
  }
}
}