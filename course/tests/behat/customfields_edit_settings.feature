@core @core_course
Feature: Teachers can edit course custom fields
  In order to have additional data on the course
  As a teacher
  I need to edit data for custom fields

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
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And I log in as "admin"
    And I navigate to "Courses > Course custom fields" in site administration
    And I select "Checkbox" from the "Add a new custom field" singleselect
    And I set the following fields to these values:
      | Name | Test field |
      | Short Name | testfield |
    And I press "Save changes"
    And I log out

  Scenario: Have a checkbox on course edit form
    When I log in as "teacher1"
     And I am on "Course 1" course homepage
    When I navigate to "Edit settings" in current page administration
    Then I should see "Category for test"
     And I should see "Test field"
