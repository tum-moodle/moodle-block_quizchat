@block @block_quizchat
Feature: prohibit and allow sendmsg capability in a quizchat block
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
    And I log in as "admin"
    And I navigate to "Users > Permissions > Define roles" in site administration
    And I follow "Student"
    And I press "Reset"
    And I click on "Allow role assignments" "checkbox"
    And I press "Continue"
    And I press "Save changes"
    And I log out
    And I close all opened windows
  @javascript
  Scenario: messages can be sent when permission is allowed
    Given I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I click on "Actions menu" "icon" in the "Quizchat" "block"
    And I follow "Permissions"
    And I wait until the page is ready
    Then "//*[contains(@id, 'permissions')]//tr[th[contains(., 'block/quizchat:sendmsg')]]//td[contains(@class, 'allowedroles') and contains(., 'Student')]" "xpath_element" should exist
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    Then I should not see "Students messages are deactivated."
    And "Help with deactivated students messages" "icon" should not exist
    And I log out
    And I close all opened windows
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    And I wait until the page is ready
    And I should not see "Sending messages is deactivated."
    And I should see "You can only chat with the course instructor."
    And "block_quizchat_input_student_send" "field" should exist
    And "block_quizchat_button_student_send" "button" should exist
    And "block_quizchat_questions_select" "select" should exist

  @javascript
  Scenario: messages can not be sent when permission is restricted
    Given I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I click on "Actions menu" "icon" in the "Quizchat" "block"
    And I follow "Permissions"
    And I wait until the page is ready
    And I click on "Delete Student role" "link" in the "block/quizchat:sendmsg" "table_row"
    And I click on "Remove" "button" in the "Confirm role change" "dialogue"
    And I wait until the page is ready
    Then "//*[contains(@id, 'permissions')]//tr[th[contains(., 'block/quizchat:sendmsg')]]//td[contains(@class, 'allowedroles') and contains(., 'Student')]" "xpath_element" should not exist
    And I wait until the page is ready
    And I am on the "Quiz 1" "mod_quiz > View" page
    Then I should see "Students messages are deactivated."
    And "Help with deactivated students messages" "icon" should exist
    And I change window size to "large"
    And I click on "Help with deactivated students messages" "icon"
    And I wait until the page is ready
    #And I pause the test until return is pressed
    And I wait "2" seconds
    And I click on "//a[@id='rolepermission_link']" "xpath_element"
    And I wait "2" seconds
    #And I pause the test until return is pressed
    And I switch to "rolepermission_link" window
    #And I start watching to see if a new page loads
    #And I switch to a second window
    And I wait until the page is ready
    Then I should see "Permissions in Block: Quizchat"
    And I close all opened windows
    And I log out
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Attempt quiz"
    And I wait until the page is ready
    And I should see "Sending messages is deactivated."
    And I should not see "You can only chat with the course instructor."
    And "block_quizchat_input_student_send" "field" should not exist
    And "block_quizchat_button_student_send" "button" should not exist
    And "block_quizchat_questions_select" "select" should not exist
