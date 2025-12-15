@block @block_quizchat
Feature: Sorting messages in Quizchat block in fullscreen view
    In order to ensure no messages are overlooked and providing a more structured communication flow in Quizchat block
    As an Instructor (a user with "sendall" capabaility)
    I should be able to clearly differentiate between private and group conversations,
    to be notified of new messages in real time and clearly see which conversation the new message belongs to,
    and view messages of each conversation separately for a more focused discussion experience.
  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 2 | C2        | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C2     | q1       | 1         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher3 | Teacher   | 3        | teacher3@example.com |
      | teacher2 | Teacher   | 2        | teacher2@example.com |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
      | student2 | Student   | 2        | student2@example.com |
      | student3 | Student   | 3        | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | teacher1 | C2     | editingteacher |
      | teacher2 | C2     | editingteacher |
      | teacher3 | C2     | teacher |
      | student1 | C2     | student |
      | student2 | C2     | student |
      | student3 | C2     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C2        | Test questions |
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I turn editing mode on
    And I add the "Quizchat..." block
    And I press "Save changes"
    And I wait until the page is ready
    And I close all opened windows
    And I log out
  @javascript
  Scenario: Recieve messages in private conversations
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
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And A Quizchat message "I have a general question." is sent from "student1" to "teachers" about question "general" at quiz "Quiz 1"
    And A Quizchat message "I'd like to ask about question 1." is sent from "student1" to "teachers" about question "TF5" at quiz "Quiz 1"
    And A Quizchat message "Students should be notified about question 2." is sent from "teacher2" to "teacher1" about question "TF1" at quiz "Quiz 1"
    And I reload the page
    And I wait until the page is ready
    And I change window size to "large"
    And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
    And I switch to a second window
    And I wait until the page is ready
    And I should see "1, Student" in the "#msg_me" "css_element"
    And I should see "2, Teacher" in the "#msg_me" "css_element"
    And I click on "1, Student" "link" in the "#msg_me" "css_element"
    And I should see "1, Student" in the ".header-container" "css_element"
    And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
    And I should see "I have a general question." in the ".block_quizchat_msg_area_body" "css_element"
    And I should see "I'd like to ask about question 1." in the ".block_quizchat_msg_area_body" "css_element"
    And I should see "Question: General" in the ".block_quizchat_msg_area_body" "css_element"
    And "TF5" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
    And "1, Student" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
    And "#fitem_id_block_quizchat_questions_select" "css_element" should be visible
    And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
    And I click on "2, Teacher" "link" in the "#msg_me" "css_element"
    And I should see "2, Teacher" in the ".header-container" "css_element"
    And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
    And I should see "Students should be notified about question 2." in the ".block_quizchat_msg_area_body" "css_element"
    And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
    And I should see "Question: TF1" in the ".block_quizchat_msg_area_body" "css_element"
    And "#fitem_id_block_quizchat_questions_select" "css_element" should be visible
    And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
  @javascript
    Scenario: Recieve messages in group conversations
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
      And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
      And A Quizchat message "Additional info for q1." is sent from "teacher2" to "group" about question "TF5" at quiz "Quiz 1"
      And A Quizchat message "Additional info for q2." is sent from "teacher2" to "group" about question "TF1" at quiz "Quiz 1"
      And A Quizchat message "Additional info for all quiz participants." is sent from "teacher2" to "all" about question "general" at quiz "Quiz 1"
      And I reload the page
      And I wait until the page is ready
      And I change window size to "large"
      And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
      And I switch to a second window
      And I wait until the page is ready
      And I should see "Group TF5" in the "#msg_all" "css_element"
      And I should see "Group TF1" in the "#msg_all" "css_element"
      And I should see "Everyone" in the "#msg_all" "css_element"
      And I click on "Group TF5" "link" in the "#msg_all" "css_element"
      And I should see "Group TF5" in the ".header-container" "css_element"
      And I should see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "To:Group TF5" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Question: TF5" in the ".block_quizchat_msg_area_body" "css_element"
      And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should not be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
      And I click on "Group TF1" "link" in the "#msg_all" "css_element"
      And I should see "Group TF1" in the ".header-container" "css_element"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "To:Group TF1" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Question: TF1" in the ".block_quizchat_msg_area_body" "css_element"
      And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should not be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
      And I click on "Everyone" "link" in the "#msg_all" "css_element"
      And I should see "Everyone" in the ".header-container" "css_element"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "To:Everyone" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Question: General" in the ".block_quizchat_msg_area_body" "css_element"
      And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should not be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
  @javascript
    Scenario: Recieve messages in all messages conversation
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
      And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
      And A Quizchat message "Additional info for q1." is sent from "teacher2" to "group" about question "TF5" at quiz "Quiz 1"
      And A Quizchat message "Additional info for q2." is sent from "teacher2" to "group" about question "TF1" at quiz "Quiz 1"
      And A Quizchat message "Additional info for all quiz participants." is sent from "teacher2" to "all" about question "general" at quiz "Quiz 1"
      And A Quizchat message "I have a general question." is sent from "student1" to "teachers" about question "general" at quiz "Quiz 1"
      And A Quizchat message "I'd like to ask about question 1." is sent from "student1" to "teachers" about question "TF5" at quiz "Quiz 1"
      And A Quizchat message "Students should be notified about question 2." is sent from "teacher2" to "teacher1" about question "TF1" at quiz "Quiz 1"
      And I reload the page
      And I wait until the page is ready
      And I change window size to "large"
      And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
      And I switch to a second window
      And I wait until the page is ready
      And I should see "Group TF5" in the "#msg_all" "css_element"
      And I should see "Group TF1" in the "#msg_all" "css_element"
      And I should see "Everyone" in the "#msg_all" "css_element"
      And I should see "1, Student" in the "#msg_me" "css_element"
      And I should see "2, Teacher" in the "#msg_me" "css_element"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "All messages" in the ".header-container" "css_element"
      And I should see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "I have a general question." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "I'd like to ask about question 1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Students should be notified about question 2." in the ".block_quizchat_msg_area_body" "css_element"
      And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "TF5" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "1, Student" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should be visible
      And I click on "Group TF5" "link" in the "#msg_all" "css_element"
      And I should see "Group TF5" in the ".header-container" "css_element"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "I have a general question." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "I'd like to ask about question 1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should not see "Students should be notified about question 2." in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should not be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should not be visible
      And I click on "All messages" "link"
      And I should see "Today" in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "All messages" in the ".header-container" "css_element"
      And I should see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for all quiz participants." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "I have a general question." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "I'd like to ask about question 1." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Students should be notified about question 2." in the ".block_quizchat_msg_area_body" "css_element"
      And "2, Teacher" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "TF5" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "1, Student" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
      And "#fitem_id_block_quizchat_questions_select" "css_element" should be visible
      And "#fitem_id_block_quizchat_users_select" "css_element" should be visible
  @javascript
    Scenario: Check notifications
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
      And I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
      And I wait until the page is ready
      And I change window size to "large"
      And I click on "Open Quizchat in a new page" "icon" in the "Quizchat" "block"
      And I switch to a second window
      And I wait until the page is ready
      And A Quizchat message "Additional info for q1." is sent from "teacher2" to "group" about question "TF5" at quiz "Quiz 1"
      And I wait "5" seconds 
      And I should see "Additional info for q1." in the ".block_quizchat_msg_area_body" "css_element"
      And I click on "Group TF5" "link" in the "#msg_all" "css_element"
      And A Quizchat message "I have a general question." is sent from "student1" to "teachers" about question "general" at quiz "Quiz 1"
      And A Quizchat message "Additional info for q2." is sent from "teacher2" to "group" about question "TF1" at quiz "Quiz 1"
      And I wait "3" seconds
      And "//a[.//strong[text()='1, Student']]//span[starts-with(@id, 'unread-count-user-')]" "xpath_element" should be visible
      And "//a[.//strong[text()='1, Student']]//span[starts-with(@id, 'unread-count-user-')]" "xpath_element" should contain "1" text
      And "//a[.//strong[text()='Group TF1']]//span[starts-with(@id, 'unread-count-question-')]" "xpath_element" should be visible
      And "//a[.//strong[text()='Group TF1']]//span[starts-with(@id, 'unread-count-question-')]" "xpath_element" should contain "1" text
      And "#all-messages-unread-count" "css_element" should be visible
      And "#all-messages-unread-count" "css_element" should contain "2" text
      And I follow "All messages"
      And I wait "3" seconds
      And I should see "I have a general question." in the ".block_quizchat_msg_area_body" "css_element"
      And I should see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
      And "#all-messages-unread-count" "css_element" should not be visible
      And "//a[.//strong[text()='Group TF1']]//span[starts-with(@id, 'unread-count-question-')]" "xpath_element" should not be visible
      And "//a[.//strong[text()='1, Student']]//span[starts-with(@id, 'unread-count-user-')]" "xpath_element" should not be visible
