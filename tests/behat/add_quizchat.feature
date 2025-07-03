@block @block_quizchat
Feature: Add a quizchat block to quiz
  In order to enhance the communication in a quiz
  As a user with capability(editingteacher or admin)
  I want to add a quizchat block to a quiz

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C1     | q1       | 1         |
    And the following "activities" exist:
      | activity   | name     | course | idnumber |
      | forum       | Forum 1 | C1     | f1      |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher2 | C1     | teacher |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext               |
      | Test questions   | truefalse   | TF1   | Text of the first question |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And I am on "Course 1" course homepage

  Scenario: Add quizchat block to quiz activity - user with capability
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat" block
    And I configure the "Quizchat" block
    And I set the following fields to these values:
      | Display on page types | Any quiz module page |
    And I press "Save changes"
    Then I should see "Quizchat" in the "Quizchat" "block"

  Scenario: Add quizchat block to non-quiz activity - user with capability
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Forum 1"
    And I turn editing mode on
    And I follow "Add a block"
    Then I should not see "Quizchat"

  Scenario: Add quizchat block to quiz - user without capability
    When I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    Then I should not see "Add a block"
