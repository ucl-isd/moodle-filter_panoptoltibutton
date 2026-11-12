@filter @filter_panoptoltibutton @editor_tiny @javascript
Feature: Use the Panopto Tiny editor button to embed videos in the text editor.
    In order to embed the Panopto video player in Moodle content
    As a student
    I need to be able to use the Panopto Tiny editor button to embed videos in the text editor.

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email          |
      | student  | Student   | User     | s1@example.com |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | student | C1     | student        |
    And the following "activities" exist:
      | course | activity | name         | assignsubmission_onlinetext_enabled | assignsubmission_file_enabled |
      | C1     | assign   | Assignment 1 |                                   1 |                             0 |
    And the "panoptoltibutton" filter is "on"
    And the following config values are set as admin:
      | automatic_operation_target_server | panopto.example.com | block_panopto |
    And the following "mod_lti > tool types" exist:
      | name    | baseurl                                                              | tooldomain          | state | lti_customparameters        |
      | Panopto | /mod/lti/tests/fixtures/tool_provider.php?server=panopto.example.com | panopto.example.com |     1 | panopto_course_embed_tool=1 |

  Scenario: Panopto videos keep their titles when saved and edited
    Given I am on the "Assignment 1" "Activity" page logged in as "student"
    And I press "Add submission"
    When I click on the "Add Panopto Video" button for the "Online text" TinyMCE editor
    And I insert the following Panopto videos:
      | title       |
      | Lecture one |
      | Lecture two |
    And I switch to the "Online text" TinyMCE editor iframe
    Then "iframe[title='Lecture one']" "css_element" should exist
    And "iframe[title='Lecture two']" "css_element" should exist
    And I switch to the main frame
    And I press "Save changes"
    Then "iframe[title='Lecture one']" "css_element" should exist
    And "iframe[title='Lecture two']" "css_element" should exist
    And I press "Edit submission"
    And I switch to the "Online text" TinyMCE editor iframe
    Then "iframe[title='Lecture one']" "css_element" should exist
    And "iframe[title='Lecture two']" "css_element" should exist
    And I switch to the main frame
