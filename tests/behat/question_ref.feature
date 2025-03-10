@block @block_quizchat
Feature: Question reference
  As an instructor/editingteacher,
  I want students to be able to reference specific questions in their messages and see clickable links to questions when referenced by a teacher,
  So that I can preview the referenced question efficiently, and students can directly navigate to the specific question when I refer a question in my messages.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1       | 0        |
    And the following "activities" exist:
      | activity   | name   | course | idnumber | showblocks|
      | quiz       | Quiz 1 | C1     | q1       | 1         |
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
      | teacher2 | C1     | teacher |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
      
  @javascript @repeated_questionref
  Scenario: Teachers sends a message with reference to the same question twice
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
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "TF5" item in the autocomplete list
    And I wait for 1 seconds
    And I set the field "block_quizchat_input_instructor_send" to "TF5 specific question to TF5 group from teacher1."
    And I press "Send"
    And I wait until the page is ready
    And I should see "TF5 specific question to TF5 group from teacher1."
    And I should see "Question: TF5"
    ##############################
    ### Repeat all these steps ###
    ##############################
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "TF5" item in the autocomplete list
    And I wait for 1 seconds
    And I set the field "block_quizchat_input_instructor_send" to "Second TF5 specific question to TF5 group from teacher1."
    And I press "Send"
    And I wait until the page is ready
    And I should see "Second TF5 specific question to TF5 group from teacher1."
    And I should see "Question: TF5"
    
  @javascript
  Scenario: student sends and teacher receives message with random question reference
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
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I wait until the page is ready
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "1" item in the autocomplete list
    And I wait for 1 seconds
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask about question 1."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask about question 1."
    #And I should see "Question: 1 - Quiz-attempt: 1"
    And "1" "link" should exist
    And I log out
    And I close all opened windows
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > Edit" page
    And I wait until the page is ready
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I should see "I'd like to ask about question 1."
    And "TF5" "link" should exist
    And I click on "TF5" "link"
    And I switch to a second window
    Then I should see "Fifth question"
  @javascript
  Scenario: shuffled question reference with click on sender name
    When the following "questions" exist:
      | questioncategory | qtype       | name | questiontext    |
      | Test questions   | truefalse   | TF1  | First question  |
      | Test questions   | truefalse   | TF2  | Second question |
      | Test questions   | truefalse   | TF3  | Third question  |
      | Test questions   | truefalse   | TF4  | Fourth question |
      | Test questions   | truefalse   | TF5  | Fifth question  |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
      | TF2      | 2    |
      | TF3      | 3    |
      | TF4      | 4    |
      | TF5      | 5    |
    And quiz "Quiz 1" contains the following sections:
      | heading   | firstslot | shuffle |
      | Section 1 | 1         | 1       |
    And I log in as "student1"
    And user "student1" has started an attempt at quiz "Quiz 1"
    And user "student1" has started an attempt at quiz "Quiz 1" with layout "2,0,4,0,1,0,3,0,5,0"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "2" item in the autocomplete list
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask about question 2."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask about question 2."
    #And I should see "Question: 2 - Quiz-attempt: 1"
    And "2" "link" should exist
    And I log out
    And I close all opened windows
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > Responses report" page
    And I wait until the page is ready
    And I should see "I'd like to ask about question 2."
    And "TF4" "link" should exist
    And "1, Student" "link" should exist
    And I click on "1, Student" "link"
    And I set the field "block_quizchat_input_instructor_send" to "Hi S1!"
    And I wait "15" seconds for element with selector ".participant-name-menu[title='1, Student']" to become visible
    And the "title" attribute of ".questions-menu" "css_element" should contain "TF4"
    And I wait "3" seconds
    And I press "Send"
    And I should see "Hi S1!"
    And I should see "To:1, Student"
    And I click on "TF4" "link"
    And I switch to a second window
    Then I should see "Fourth question"
    And I switch to the main window
    And I log out
    And I close all opened windows
    And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And user "student1" has started an attempt at quiz "Quiz 1"
    And I should see "Hi S1!"
  @javascript
  Scenario: student sends and teacher receives message with general question
    When the following "questions" exist:
      | questioncategory | qtype       | name                      | questiontext    |
      | Test questions   | truefalse   | TF1                       | First question  |
      | Test questions   | truefalse   | TF2                       | Second question |
      | Test questions   | truefalse   | TF3                       | Third question  |
      | Test questions   | truefalse   | TF4                       | Fourth question |
      | Test questions   | truefalse   | TF5                       | Fifth question  |
    And quiz "Quiz 1" contains the following questions:
      | question                | page |
      | TF1                     | 1    |
      | TF2                     | 2    |
      | TF3                     | 3    |
      | TF4                     | 4    |
      | TF5                     | 5    |
    And quiz "Quiz 1" contains the following sections:
      | heading   | firstslot | shuffle |
      | Section 1 | 1         | 1       |
    And I log in as "student1"
    And user "student1" has started an attempt at quiz "Quiz 1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask a general question."
    And I press "Send"
    And I wait until the page is ready
    And I should see "Question: General"
    And "general" "link" should not exist
  @javascript
  Scenario: student sends and teacher receives message with shuffled question reference and more than one attempt
    When the following "questions" exist:
      | questioncategory | qtype       | name | questiontext    |
      | Test questions   | truefalse   | TF1  | First question  |
      | Test questions   | truefalse   | TF2  | Second question |
      | Test questions   | truefalse   | TF3  | Third question  |
      | Test questions   | truefalse   | TF4  | Fourth question |
      | Test questions   | truefalse   | TF5  | Fifth question  |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
      | TF2      | 2    |
      | TF3      | 3    |
      | TF4      | 4    |
      | TF5      | 5    |
    And quiz "Quiz 1" contains the following sections:
      | heading   | firstslot | shuffle |
      | Section 1 | 1         | 1       |
    And I log in as "student1"
    And user "student1" has started an attempt at quiz "Quiz 1"
    And user "student1" has started an attempt at quiz "Quiz 1" with layout "2,0,4,0,1,0,3,0,5,0"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "2" item in the autocomplete list
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask about question 2."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask about question 2."
    #And I should see "Question: 2 - Quiz-attempt: 1"
    And "2" "link" should exist
    And I should see "- Quiz-attempt: 1"
    And user "student1" has started an attempt at quiz "Quiz 1"
    And user "student1" has started an attempt at quiz "Quiz 1" with layout "5,0,3,0,1,0,2,0,4,0"
    And user "student1" has finished an attempt at quiz "Quiz 1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    And I open the autocomplete suggestions list
    And I wait for 1 seconds
    And I click on "2" item in the autocomplete list
    And I set the field "block_quizchat_input_student_send" to "I'd like to ask about question 2."
    And I press "Send"
    And I wait until the page is ready
    And I should see "I'd like to ask about question 2."
    #And I should see "Question: 2 - Quiz-attempt: 2"
    And "2" "link" should exist
    And I should see "- Quiz-attempt: 2"
    And I log out
    And I close all opened windows
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > Responses report" page
    And I wait until the page is ready
    And I should see "I'd like to ask about question 2."
    And "TF4" "link" should exist
    And "TF3" "link" should exist
    And I click on "TF3" "link"
    And I switch to a second window
    Then I should see "Third question"
  @javascript
  Scenario: Recieving messages in students view with refernced question
  When the following "questions" exist:
    | questioncategory | qtype       | name                      | questiontext    |
    | Test questions   | truefalse   | TF1                       | First question  |
    | Test questions   | truefalse   | TF2                       | Second question |
    | Test questions   | truefalse   | TF3                       | Third question  |
    | Test questions   | truefalse   | TF4                       | Fourth question |
    | Test questions   | truefalse   | TF5                       | Fifth question  |
    | Test questions   | random      | Random (Test questions)   | 0               |
  And quiz "Quiz 1" contains the following questions:
    | question                | page | requireprevious | displaynumber |
    | Random (Test questions) | 1    | 0               | 1.a           |
    | TF1                     | 2    | 1               | 1.b           |
  And user "student1" has started an attempt at quiz "Quiz 1" randomised as follows:
    | slot | actualquestion | response |
    |   1  | TF5            | False    |
    |   2  | TF1            | False    |
  And I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
  And A Quizchat message "Additional info for q2." is sent from "teacher1" to "group" about question "TF1" at quiz "Quiz 1"
  And I reload the page
  And I wait until the page is ready
  And I press "Continue your attempt"
  And I should see "Additional info for q2." in the ".block_quizchat_msg_area_body" "css_element"
  And I should see "Fifth question"
  And "1.b" "link" should exist in the ".block_quizchat_msg_area_body" "css_element"
  And I click on "1.b" "link" in the ".block_quizchat_msg_area_body" "css_element"
  And I wait until the page is ready
  Then I should see "First question"
