@block @block_quizchat
Feature: send and recieve a message in a quizchat
  In order to send a message during a quiz to all quiz participants
  As a teacher with sendall-capability(editingteacher or admin)
  I want to send a message to all quiz participants

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 2 | C2       | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C2     | q1       | 1         |
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
      | teacher2 | C2     | teacher |
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
  Scenario: send quizchat message to all - user with sendall capability
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "I'm teacher 1"
    And I press "Send"
    #And I pause the test until return is pressed
    Then I should see "I'm teacher 1"
    And I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Preview quiz"
    Then I should see "I'm teacher 1"
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    Then I should see "I'm teacher 1"

  @javascript
  Scenario: send quizchat message to teachers group - user without sendall capability
    When I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Preview quiz"
    And I set the field "block_quizchat_input_student_send" to "I'm teacher 2"
    And I press "Send"
    Then I should see "I'm teacher 2"
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > Edit" page
    Then I should see "I'm teacher 2"
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    Then I should not see "I'm teacher 1"

  @javascript
  Scenario: send quizchat msg from a user with sendall capability to a specific user
    When I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I expand the "Send message to:" autocomplete
    And I wait for 1 seconds
    And I click on "2, Teacher" item in the autocomplete list
    And I set the field "block_quizchat_input_instructor_send" to "Hello Teacher 2!"
    And I press "Send"
    Then I should see "Hello Teacher 2!"
    And I log in as "teacher2"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Preview quiz"
    Then I should see "Hello Teacher 2!"
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    Then I should not see "Hello Teacher 2!"
