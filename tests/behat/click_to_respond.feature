@block @block_quizchat
Feature: Click to respond.
  When logged in as editingteacher I want to be able to send a message to
  another user specifically who sent a message to me or to the teachers group
  selecting the recipient by clicking on their name in the message header.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 2 | C2       | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C2     | q1       | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher2 | Teacher2   | 2        | teacher2@example.com |
      | teacher1 | Teacher1   | 1        | teacher1@example.com |
      | teacher3 | Teacher3   | 3        | teacher1@example.com |
      | student1 | Student1   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher2 | C2     | teacher |
      | teacher1 | C2     | editingteacher |
      | teacher3 | C2     | editingteacher |
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
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I close all opened windows
    And I log out
    And I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Preview quiz"
    And I wait until the page is ready
    And I set the field "block_quizchat_input_student_send" to "I'm teacher 2"
    And I press "Send"
    And I wait "2" seconds
    And I close all opened windows
    And I log out
    And I am on the "Quiz 1" "mod_quiz > Edit" page logged in as "teacher1"
    And I wait until the page is ready
    And the "title" attribute of ".questions-menu" "css_element" should contain "General"
    And I set the field "block_quizchat_input_instructor_send" to "I'm teacher 1"
    And I click on "2, Teacher" "link"
    And I wait until "[role=option][data-active-selection=true] .participant-name-menu[title*=Teacher2]" "css_element" exists
    And I press "Send"

  @javascript @clicktorespond @messagereceived @norespondlink
  Scenario: Click to respond - message is received by the recipient - should not see a respond link.
    When I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    Then I should see "I'm teacher 1"
    And "1, Teacher" "link" should not exist

  @javascript @clicktorespond @messagenotreceived @norespondlink
  Scenario: Click to respond - message is received by the recipient but not by other users - should not see a respond link.
    When I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    And I wait until the page is ready
    Then I should not see "I'm teacher 1"
    And "1, Teacher" "link" should not exist

  @javascript @clicktorespond @messagereceived @respondlink
  Scenario: Click to respond - message sent by one teacher is received by other teachers on the instructors group.
    When I log in as "teacher3"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    Then I should see "I'm teacher 1"
    And I should see "I'm teacher 2"
    And "2, Teacher" "link" should exist
    Then "1, Teacher" "link" should exist
