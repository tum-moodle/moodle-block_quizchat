@block @block_quizchat
Feature: Quizchat Availability
  In order to send a message during a quiz
  As a student
  I want to be able to send a message after starting quiz attempt.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1       | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C1     | q1       | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher2 | C1     | teacher |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name                      | questiontext    |
      | Test questions   | truefalse   | TF1                       | First question  |
      | Test questions   | truefalse   | TF2                       | Second question |
    And quiz "Quiz 1" contains the following questions:
      | question                | page |
      | TF1                     | 1    |
      | TF2                     | 2    |
  @javascript
  Scenario: check availability in noneditting teacher view after and before preview attempt
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "I'm teacher 1"
    And I press "Send"
    And I should see "I'm teacher 1"
    And I close all opened windows
    And I log out
    And I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    Then I should see "Quizchat will not be available until quiz attempt starts."
    And I should not see "You can only chat with the course instructor."
    And I should not see "1000 character(s) remaining."
    And I should not see "I'm teacher 1"
    And "block_quizchat_input_student_send" "field" should not exist
    And "block_quizchat_button_student_send" "button" should not exist
    And "block_quizchat_questions_select" "select" should not exist
    And I press "Preview quiz"
    And I should not see "Quizchat is only available after the quiz attempt."
    And I should see "You can only chat with the course instructor."
    And I should see "1000 character(s) remaining."
    And I should see "I'm teacher 1"
    And the "block_quizchat_input_student_send" "field" should be enabled
    And the "block_quizchat_button_student_send" "button" should be enabled
    And "block_quizchat_questions_select" "select" should exist
  @javascript
  Scenario: check availability in student view after and before quiz attempt
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "I'm teacher 1"
    And I press "Send"
    And I should see "I'm teacher 1"
    And I close all opened windows
    And I log out
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    Then I should see "Quizchat will not be available until quiz attempt starts."
    And I should not see "You can only chat with the course instructor."
    And I should not see "1000 character(s) remaining."
    And I should not see "I'm teacher 1"
    And "block_quizchat_input_student_send" "field" should not exist
    And "block_quizchat_button_student_send" "button" should not exist
    And "block_quizchat_questions_select" "select" should not exist
    And I press "Attempt quiz"
    And I should not see "Quizchat is only available after the quiz attempt."
    And I should see "You can only chat with the course instructor."
    And I should see "1000 character(s) remaining."
    And I should see "I'm teacher 1"
    And the "block_quizchat_input_student_send" "field" should be enabled
    And the "block_quizchat_button_student_send" "button" should be enabled
    And "block_quizchat_questions_select" "select" should exist
  @javascript
  Scenario: check availability in student view if chat is open before quiz starting
    When I log in as "admin"
    #And I navigate to "Location > Location settings" in site administration
    And I follow "Site administration"
    And I set the field "Search" to "Location settings"
    And I press "Search"
    And I wait until the page is ready
    And I follow "Location settings"
    And I set the following fields to these values:
      | Default timezone | Europe/Berlin |
      | Default country  | Germany       |
    And I press "Save changes"
    And the following "activities" exist:
      | activity   | name   | intro              | course | idnumber | timeopen              | timeclose            | timelimit | attempts | showblocks|
      | quiz       | Quiz 2 | Quiz 2 description | C1     | quiz2    | ## now +3 minutes ##  | ## now +4 minutes ## | 60        | 1        | 1         |
    And quiz "Quiz 2" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And I am on the "Quiz 2" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I set the field with xpath "//input[@type='checkbox' and @name='config_enableopenbefore']" to "1"
    And I set the field with xpath "//input[@type='text' and @name='config_openbefore[number]']" to "1"
    And I press "Save changes"
    And I am on the "Quiz 2" "mod_quiz > View" page
    And I close all opened windows
    And I log out
    And I am on the "Quiz 2" "mod_quiz > View" page logged in as "student1"
    And I wait until the page is ready
    And I wait until Quizchat is available at quiz "Quiz 2"
    And I wait until the page is ready
    Then I should not see "Quizchat will not be available until quiz attempt starts."
    And I should see "1000 character(s) remaining."
    And "Attempt quiz" "button" should not exist
    And I wait "60" seconds
    And I reload the page
    Then "Attempt quiz" "button" should exist
    And I press "Attempt quiz"
    And I press "Start attempt"
    And I wait until "Finished" "text" exists
    And I should not see "1000 character(s) remaining."
    And I should see "Access to Quizchat is restricted until 1 minutes before the quiz start time."


