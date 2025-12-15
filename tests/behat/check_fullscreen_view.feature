@block @block_quizchat
Feature: Add an icon to display Quizchat block in fullscreen
    In order to enhance the visual representation of a Quizchat block
    As an Instructor or a user with sendall capabaility
    I should be able to view quizchat in fullscreen mode

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 2 | C2        | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C2     | q1       | 1         |
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
      | questioncategory | qtype       | name          | questiontext    |
      | Test questions   | truefalse   | TF1           | First question  |
      | Test questions   | truefalse   | TF2           | Second question |
    And quiz "Quiz 1" contains the following questions:
      | question                | page |
      | TF1                     | 1    |
      | TF2                     | 2    |
  @javascript
  Scenario: fullscreen icon visibility
    Given I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And "Open Quizchat in a new page" "icon" should exist in the "Quizchat" "block"
    And I turn editing mode on
    And I wait until the page is ready
    And "Open Quizchat in a new page" "icon" should exist in the "Quizchat" "block"
    And I change window size to "large"
    And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
    And I switch to a second window
    Then I should see "Quizchat"
    And I close all opened windows
    And I log out
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I wait until the page is ready
    And "Open Quizchat in a new page" "icon" should not exist in the "Quizchat" "block"
    And I close all opened windows
    And I log out
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher2"
    And I wait until the page is ready
    And "Open Quizchat in a new page" "icon" should not exist in the "Quizchat" "block"

  @javascript
  Scenario: enable fullscreen icon
    Given I log in as "teacher1"
    And I wait until the page is ready
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I click on "[id^='action-menu-toggle']" "css_element" in the ".block_quizchat" "css_element"
    And I follow "Permissions"
    And I wait until the page is ready
    And I click on ".allowlink" "css_element" in the "block/quizchat:sendall" "table_row"
    And I press "Non-editing teacher"
    And I wait until the page is ready
    Then "//*[contains(@id, 'permissions')]//tr[th[contains(., 'block/quizchat:sendall')]]//td[contains(@class, 'allowedroles') and contains(., 'Non-editing teacher')]" "xpath_element" should exist
    And I log out
    And I log in as "teacher2"
    And I wait until the page is ready
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And "Open Quizchat in a new page" "icon" should exist in the "Quizchat" "block"
