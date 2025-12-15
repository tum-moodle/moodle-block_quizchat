@block @block_quizchat
Feature: Message date validation and grouping of messages
    In order to ensure messages are displayed correctly
    As a user
    I want to verify that messages are grouped by day and today's date appears as Today

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 2 | C2       | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C2     | q1       | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C2     | editingteacher |
      | student1 | C2     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C2        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name                      | questiontext    |
      | Test questions   | truefalse   | TF1                       | First question  |
      | Test questions   | truefalse   | TF2                       | Second question |
    And quiz "Quiz 1" contains the following questions:
      | question                | page |
      | TF1                     | 1    |
      | TF2                     | 2    |
  @javascript
  Scenario: check quizchat message date
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "This is my first message."
    And I press "Send"
    Then I should see "This is my first message."
    And I should see "Today"
    And the last quizchat message date is set to "5" days back
    And I reload the page
    And I wait until the page is ready
    And I should not see "Today"
    And I should see "5" days back in the selector ".line-with-text"
    And I copy the last quizchat message "2" times 
    And I reload the page
    And I wait until the page is ready
    And I should not see "Today"
    And I should see "5" days back in the selector ".line-with-text"
    And I set the field "block_quizchat_input_instructor_send" to "This is my second message."
    And I press "Send"
    Then I should see "This is my second message."
    And I should see "Today"
    And I should see "5" days back in the selector ".line-with-text"
    And The count of date classes should be "2"
