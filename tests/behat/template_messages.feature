@block @block_quizchat
Feature: Quizchat  Template Messages Menu for Instructors
    As an instructor,
    I want a menu within the block that provides quick access to pre-defined template messages,
    So that I can respond to common student queries efficiently without typing the same messages repeatedly.

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
  Scenario: Enable and disable the Template Messages Menu in block and site level
    Given I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    Then I should not see "Select template message"
    And I log out
    And I close all opened windows
    Then I am logged in as "admin"
    And I follow "Site administration"
    And I set the field "Search" to "quizchat"
    And I press "Search"
    And I wait until the page is ready
    And I follow "Quizchat"
    And I set the field "Enable template menu" to "1"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Changes saved"
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    Then I should see "Select template message"
    And I turn editing mode on
    And I configure the "Quizchat" block
    And I set the following fields to these values:
      | config_templatemsgsmenu | 0 |
    And I press "Save changes"
    And I wait until the page is ready
    And I should see "Quizchat settings have been saved successfully."
    Then I should not see "Select template message"

@javascript
  Scenario: Central Template Messages
    Given I am logged in as "admin"
    And I follow "Site administration"
    And I set the field "Search" to "quizchat"
    And I press "Search"
    And I wait until the page is ready
    And I follow "Quizchat"
    And I follow "Create template message"
    And I should see "100 character(s) remaining." in the "#quizchat-form" "css_element"
    And I should see "Save" in the "#quizchat-form" "css_element"
    And I should not see "Create template message" in the "#quizchat-form" "css_element"
    And I set the field "quizchat-title" to "CT1"
    And I set the field "quizchat-message" to "Template1"
    And I press "Save"
    And I set the field "Enable template menu" to "1"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Changes saved"
    Then I should see "Template1"
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I expand the "Select template message" autocomplete
    And I wait for 1 seconds
    And I click on "CT1" item in the autocomplete list
    And I press "Send"
    And I wait for 1 seconds
    Then I should see "Template1"
    And I expand the "Select template message" autocomplete
    And I wait for 1 seconds
    And I click on "Restroom Rules" item in the autocomplete list
    And I press "Send"
    And I wait for 1 seconds
    Then I should see "If you need to use the restroom, please request permission before leaving your seat."
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I wait until the page is ready
    Then I should see "Template1"
    And I should see "If you need to use the restroom, please request permission before leaving your seat."

@javascript
  Scenario: Quiz Template Messages
    Given I am logged in as "admin"
    And I follow "Site administration"
    And I set the field "Search" to "quizchat"
    And I press "Search"
    And I wait until the page is ready
    And I follow "Quizchat"
    And I set the field "Enable template menu" to "1"
    And I press "Save changes"
    And I wait until the page is ready
    Then I should see "Changes saved"
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I configure the "Quizchat" block
    And I expand all fieldsets
    And I should see "No template messages found."
    And I follow "Create template message"
    And I should see "100 character(s) remaining." in the "#quizchat-form" "css_element"
    And I should see "Save" in the "#quizchat-form" "css_element"
    And I should not see "Create template message" in the "#quizchat-form" "css_element"
    And I set the field "quizchat-title" to "BT1"
    And I set the field "quizchat-message" to "Quiz Template 1"
    And I press "Save"
    And I press "Save changes"
    And I wait until the page is ready
    And I should see "Quizchat settings have been saved successfully."
    And I expand the "Select template message" autocomplete
    And I wait for 1 seconds
    And I click on "BT1" item in the autocomplete list
    And I press "Send"
    And I wait for 1 seconds
    Then I should see "Quiz Template 1"
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I wait until the page is ready
    Then I should see "Quiz Template 1"