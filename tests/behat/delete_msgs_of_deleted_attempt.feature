@block @block_quizchat
Feature: Delete messages of deleted attempt
  As an instructor/editingteacher,
  I want messages that have refernce to a question in a deleted attempt to be deleted also from Quizchat messages.

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
  @javascript
  Scenario: student sends and teacher receives message with random question reference
    When the following "questions" exist:
      | questioncategory | qtype       | name                      | questiontext    |
      | Test questions   | truefalse   | TF1                       | First question  |
      | Test questions   | truefalse   | TF2                       | Second question |
      | Test questions   | truefalse   | TF3                       | Third question  |
      | Test questions   | truefalse   | TF4                       | Fourth question |
      | Test questions   | truefalse   | TF5                       | Fifth question  |
      | Test questions   | random      | Random (Test questions)   | 0               |
    And quiz "Quiz 1" contains the following questions:
      | question                | page | requireprevious |
      | Random (Test questions) | 1    | 0               |
      | TF1                     | 1    | 1               |
    And user "student1" has started an attempt at quiz "Quiz 1" randomised as follows:
      | slot | actualquestion | response |
      |   1  | TF5            | False    |
      |   2  | TF1            | False    |
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I wait until the page is ready
    And I press "Continue your attempt"
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask general question."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask general question."
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "1" item in the autocomplete list
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask about question 1."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask about question 1."
    And I should see "Question: 1 - Quiz-attempt: 1"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit" "button" in the "Submit all your answers and finish?" "dialogue"
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I navigate to "Results" in current page administration
    And I wait until the page is ready
    And I should see "I'd like to ask general question."
    And I should see "I'd like to ask about question 1."
    And I click on "mod-quiz-report-overview-report-selectall-attempts" "checkbox"
    And I click on "Delete selected attempts" "button"
    And I click on "Yes" "button"
    And I wait until the page is ready
    And I should not see "I'd like to ask about question 1."
    And I should see "I'd like to ask general question."    
