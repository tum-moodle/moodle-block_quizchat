@block @block_quizchat
Feature: Uninstall quizchat block plugin.
  It has to be possible to uninstall the plugin - even if one ore more block instances exist in any of the quizzes.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C1     | q1       | 1         |
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


@uninstall_plugin_with_instance @javascript
  Scenario: Uninstall the block_quizchat plugin without removing instances first.
    When I log in as "admin"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I turn editing mode on
    And I wait until the page is ready
    And I add the "Quizchat..." block
    And I click on "Save changes" "button"
    And I navigate to "Plugins > Plugins overview" in site administration
    And I wait until the page is ready
    And I click on "Uninstall" "link" in the "block_quizchat" "table_row"
    And I wait until the page is ready
    And I click on "Continue" "button"
    And I wait until the page is ready
    Then I should see "Uninstalling block_quizchat"
    And I should see "Success"
    And I should see "All data associated with the plugin block_quizchat has been deleted from the database."
