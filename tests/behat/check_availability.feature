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
    And the following "blocks" exist:
      | blockname| contextlevel    | reference | pagetypepattern | defaultregion |
      | quizchat | Activity module | q1        | mod-quiz-*      | side-pre      |
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
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "I'm teacher 1"
    And I press "Send"
    And I should see "I'm teacher 1"
  @javascript
  Scenario: check availability in noneditting teacher view after and before preview attempt
    When I log in as "teacher2"
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
    When I log in as "student1"
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
