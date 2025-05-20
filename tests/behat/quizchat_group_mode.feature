@block @block_quizchat
Feature: Viewing Quizchat messages by group
  In order to view Quizchat messages on a large course
  As a teacher
  I need to filter messages by group

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "groups" exist:
      | name    | course | idnumber | participation |
      | Group 1 | C1     | G1       | 1             |
      | Group 2 | C1     | G2       | 1             |
      | Group 3 | C1     | G3       | 1             |
      | Group 4 | C1     | G4       | 1             |
    And the following "groupings" exist:
      | name       | course | idnumber |
      | Grouping 1 | C1     | GG1      |
    And the following "grouping groups" exist:
      | grouping | group |
      | GG1      | G1    |
      | GG1      | G2    |
    And the following "users" exist:
      | username   | firstname     | lastname | email                  |
      | teacher1   | TeacherG1     | 1        | teacher1@example.com   |
      | user1      | User1G1       | 1        | user1@example.com      |
      | user2      | User2G2       | 2        | user2@example.com      |
      | user3      | User3None     | 3        | user3@example.com      |
      | user4      | User4NPgroup  | 4        | user4@example.com      |
      | user5      | User5G4       | 5        | user5@example.com      |
    And the following "course enrolments" exist:
      | user       | course | role           |
      | teacher1   | C1     | editingteacher |
      | user1      | C1     | student        |
      | user2      | C1     | student        |
      | user3      | C1     | student        |
      | user4      | C1     | student        |
      | user5      | C1     | student        |
    And the following "group members" exist:
      | user       | group |
      | teacher1   | G1    |
      | user1      | G1    |
      | user2      | G2    |
      | user4      | G3    |
      | user5      | G4    |
    And the following "activities" exist:
      | activity | name    | intro                     | course | idnumber | groupmode | showblocks | grouping |
      | quiz     | quiz 1  | quiz with separate groups | C1     | quiz1    | 1         |    1       |          |
      | quiz     | quiz 2  | quiz without groups       | C1     | quiz2    | 0         |    1       |          |
      | quiz     | quiz 3  | quiz without grouping     | C1     | quiz3    | 1         |    1       |   GG1    |
    And the following "blocks" exist:
      | blockname| contextlevel    | reference | pagetypepattern | defaultregion |
      | quizchat | Activity module | quiz1        | mod-quiz-*      | side-pre      |
      | quizchat | Activity module | quiz2        | mod-quiz-*      | side-pre      |
      | quizchat | Activity module | quiz3        | mod-quiz-*      | side-pre      |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name  | questiontext    |
      | Test questions   | truefalse   | TF1   | First question  |
      | Test questions   | truefalse   | TF2   | Second question |
    And quiz "quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And quiz "quiz 2" contains the following questions:
      | question | page |
      | TF1      | 1    |
    And quiz "quiz 3" contains the following questions:
      | question | page |
      | TF1      | 1    |

  @javascript
  Scenario Outline: Groups selector visibility in block view in different quiz pages with and without group mode
    Given I am on the "<quiz>" "<quizpage>" page logged in as "teacher1"
    Then "group" "select" <selectorexist> in the "Quizchat" "block"

    Examples:
      | quiz   | quizpage                         | selectorexist    |
      | quiz 1 | mod_quiz > View                  | should exist     |
      | quiz 1 | mod_quiz > Edit                  | should exist     |
      | quiz 1 | mod_quiz > User overrides        | should exist     |
      | quiz 1 | mod_quiz > Group overrides       | should exist     |
      | quiz 1 | mod_quiz > Responses report      | should not exist |
      | quiz 1 | mod_quiz > Statistics report     | should not exist |
      | quiz 1 | mod_quiz > Manual grading report | should not exist |
      | quiz 2 | mod_quiz > View                  | should not exist |

@javascript
  Scenario Outline: Groups selector visibility in fullscreen view with and without group mode
    Given I am on the "<quiz>" "mod_quiz > View" page logged in as "teacher1"
    And I change window size to "large"
    And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
    And I switch to a second window
    Then "group" "select" <selectorexist> in the "#fullscreen-page" "css_element"
    And I close all opened windows

    Examples:
      | quiz   | selectorexist    |
      | quiz 1 | should exist     |
      | quiz 2 | should not exist |

@javascript
  Scenario Outline: Messages Filtering with group mode and grouping
    Given I am on the "<quiz>" "mod_quiz > View" page logged in as "teacher1"
    And I select "<group>" from the "Separate groups" singleselect
    And I set the field "block_quizchat_input_instructor_send" to "<msg>"
    And I press "Send"
    And I wait for 2 seconds
    Then I should see "<msg>"
    And "<receiver>" "text" should exist in the ".msg-header" "css_element"
    And I close all opened windows
    And I log out
    And I am on the "<quiz>" "mod_quiz > View" page logged in as "user1"
    And I press "Attempt quiz"
    #And I pause the test until return is pressed
    Then I <visible1> "<msg>"
    And I close all opened windows
    And I log out
    And I am on the "<quiz>" "mod_quiz > View" page logged in as "user2"
    And I press "Attempt quiz"
    #And I pause the test until return is pressed
    Then I <visible2> "<msg>"
    And I close all opened windows
    And I log out
    And I am on the "<quiz>" "mod_quiz > View" page logged in as "user4"
    And I press "Attempt quiz"
    Then I <visible4> "<msg>"

    Examples:
      | quiz   | group            | msg                                 | receiver   | visible1       | visible2       | visible4       |
      | quiz 1 | All participants | This is an All-participants message | Everyone   | should see     | should see     | should see     |
      | quiz 1 | Group 1          | This is a G1 message                | Group 1    | should see     | should not see | should not see |
      | quiz 1 | Group 2          | This is a G2 message                | Group 2    | should not see | should see     | should not see |
      | quiz 3 | Group 2          | This is a G2 message                | Group 2    | should not see | should see     | should not see |
      | quiz 3 | All participants | This is a GG1 message               | Grouping 1 | should see     | should see     | should not see |

@javascript
  Scenario Outline: Test the impact of group or grouping renaming on previously sent message headers
    Given I am on the "<quiz>" "mod_quiz > View" page logged in as "teacher1"
    And I select "<gmenuselection>" from the "Separate groups" singleselect
    And I set the field "block_quizchat_input_instructor_send" to "<msg>"
    And I press "Send"
    And I wait for 2 seconds
    Then I should see "<msg>"
    And "<gname>" "text" should exist in the ".msg-header" "css_element"
    And I close all opened windows
    And I log out
    And I am on the "<quiz>" "mod_quiz > View" page logged in as "user1"
    And I press "Attempt quiz"
    Then I should see "<msg>"
    And "<gname>" "text" should exist in the ".msg-header" "css_element"
    And I close all opened windows
    And I log out
    And I am on the "Course 1" "<page>" page logged in as "teacher1"
    And I <gselect>
    And I <editclick>
    And I set the following fields to these values:
      | <fieldname> name | <updatedgname> |
    When I press "Save changes"
    Then I should see "<updatedgname>"
    And I am on the "<quiz>" "mod_quiz > View" page
    Then I should see "<msg>"
    And "<updatedgname>" "text" should exist in the ".msg-header" "css_element"
    And I close all opened windows
    And I log out
    And I am on the "<quiz>" "mod_quiz > View" page logged in as "user1"
    Then I should see "<msg>"
    And "<updatedgname>" "text" should exist in the ".msg-header" "css_element"
    
    Examples:
      | quiz   | page      | gname      | updatedgname    | msg                   | gselect                                                | editclick                   | fieldname | gmenuselection    |
      | quiz 1 | groups    | Group 1    | My Group 1      | This is a G1 message  | set the field "groups" to "Group 1"                    | press "Edit group settings" | Group     |  Group 1          |
      | quiz 3 | groupings | Grouping 1 | My Grouping 1   | This is a GG1 message | click on "Edit" "link" in the "Grouping 1" "table_row" | wait for 0 seconds          | Grouping  |  All participants |

  @javascript
  Scenario: Validate message delivery in a group added after All-Participant message was sent in grouping
    Given I am on the "quiz 3" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | Access restrictions | Grouping: Grouping 1 |
    And I press "Save and display"
    And I set the field "block_quizchat_input_instructor_send" to "This is a GG1 message"
    And I press "Send"
    And I wait for 2 seconds
    Then I should see "This is a GG1 message"
    And I am on the "quiz 3" "mod_quiz > View" page logged in as "user5"
    Then I should see "Not available unless: You belong to a group in Grouping 1"
    And I should not see "This is a GG1 message"
    And I am on the "Course 1" "groupings" page logged in as "teacher1"
    And I click on "Show groups in grouping" "link" in the "Grouping 1" "table_row"
    And I set the field "addselect" to "Group 4"
    And I press "Add"
    And I press "Back to groupings"
    And user "user5" has started an attempt at quiz "quiz 3"
    And I log in as "user5"
    And I am on the "quiz 3" "mod_quiz > View" page
    And I wait for 5 seconds
    Then I should see "This is a GG1 message"
    And "Grouping 1" "text" should exist in the ".msg-header" "css_element"

 @javascript
  Scenario: Validate receiver details for deleted group messages
    Given I am on the "quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I select "Group 1" from the "Separate groups" singleselect
    And I set the field "block_quizchat_input_instructor_send" to "This is a G1 message"
    And I press "Send"
    And I wait for 2 seconds
    Then I should see "This is a G1 message"
    And "Group 1" "text" should exist in the ".msg-header" "css_element"
    And I close all opened windows
    And I log out
    And I am on the "quiz 1" "mod_quiz > View" page logged in as "user1"
    And I press "Attempt quiz"
    Then I should see "This is a G1 message"
    And "Group 1" "text" should exist in the ".msg-header" "css_element"
    And I am on the "Course 1" "groups" page logged in as "teacher1"
    And I set the field "groups" to "Group 1"
    And I press "Delete"
    And I press "Yes"
    Then I should not see "Group 1"
    And I am on the "quiz 1" "mod_quiz > Edit" page
    #And I pause the test until return is pressed
    Then I should see "This is a G1 message"
    And "Group 1 (deleted)" "text" should exist in the ".msg-header" "css_element"
    And I am on the "quiz 1" "mod_quiz > View" page logged in as "user1"
    And I should not see "This is a G1 message"