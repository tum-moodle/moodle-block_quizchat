@block @block_quizchat
Feature: MathJax typesetting
  When mathjax content is posted to the quiz chat within the configured
  delimiters it should be typeset and displayed correctly. This feature
  has to be available in different quiz views like info, review, etc.

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
    And the following config values are set as admin:
      | config                  | value           | plugin          |
      | unnotify_timeout        | 60              | block_quizchat  |

    And user "student1" has started an attempt at quiz "Quiz 1"
    And I log in as "teacher1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I press "Preview quiz"
    And I wait until the page is ready
    And I set the field "block_quizchat_input_instructor_send" to "When \(a \ne 0\), there are two solutions to \(ax^2 + bx + c = 0\) and they are $$x = {-b \pm \sqrt{b^2-4ac} \over 2a}.$$"
    And I press "Send"

  @javascript @mathjax_typesetting @student
  Scenario: MathJax typesetting - When I receive a message with MathJax
      content within the defined delimiters it should be typeset and rendered
    When I log out
    And I log in as "student1"
    And I am on the "Quiz 1" "mod_quiz > View" page
    And I wait until the page is ready
    Then I should not see "$$x = {-b \pm \sqrt{b^2-4ac} \over 2a}.$$"
    And ".MathJax" "css_element" should exist in the ".block_quizchat_msg_area_body" "css_element"
    And ".MathJax" "css_element" should exist in the ".toast-wrapper" "css_element"

  @javascript @mathjax_typesetting @typeset_card-body @teacher @mod_quiz-pagetypes
  Scenario Outline: MathJax typesetting - When I have sent a message with MathJax
      content within the defined delimiters it should be typeset and rendered
    And I log in as "teacher1"
    And I am on the <qpage> page
    And I wait until the page is ready
    Then I should not see "$$x = {-b \pm \sqrt{b^2-4ac} \over 2a}.$$"
    And ".MathJax" "css_element" should exist in the ".block_quizchat_msg_area_body" "css_element"

    Examples:
      | qpage                                               |
      | "Quiz 1" "mod_quiz > View"                          |
      | "Quiz 1" "mod_quiz > Edit"                          | 
      | "Quiz 1" "mod_quiz > Group overrides"               |
      | "Quiz 1" "mod_quiz > User overrides"                |
      | "Quiz 1" "mod_quiz > Responses report"              |
      | "Quiz 1" "mod_quiz > Manual grading report"         |
      | "Quiz 1" "mod_quiz > Statistics report"             |
      | "Quiz 1 > student1 > 1" "mod_quiz > Attempt review" |