@core @core_course @course_customfield
Feature: The course custom fields can be mandatory or not
  In order to make users required to fill a custom field
  As a manager
  I can change the requiredness of the fields

  Background:
    Given the following "course custom field categories" exist:
      | name |
      | Category for test |
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1 | topics |
      | Course 2 | C2 | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | teacher1 | C2 | editingteacher |
    When I log in as "admin"
     And I navigate to "Courses > Course custom fields" in site administration
     And I click on "Add a new custom field" "link"
     And I click on "Text field" "link"
     And I set the following fields to these values:
       | Name | Test field |
       | Short name | testfield |
       | Unique data | Yes |
     And I press "Save changes"
     And I log out

  Scenario: A course custom field with unique data must not allow same data in same field in different courses
    When I log in as "teacher1"
     And I am on "Course 1" course homepage
     And I navigate to "Edit settings" in current page administration
     And I set the following fields to these values:
       | Test field | testcontent |
     And I press "Save and display"
     And I am on "Course 2" course homepage
     And I navigate to "Edit settings" in current page administration
     And I set the following fields to these values:
       | Test field | testcontent |
     And I press "Save and display"
    Then I should see "This value is already used"
