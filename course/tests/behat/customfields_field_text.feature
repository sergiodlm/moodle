@core @core_course @core_customfield
Feature: Managers can manage course custom fields
  In order to have additional data on the course
  As a manager
  I need to create, edit, remove and sort custom fields

  Background:
    Given the following "course custom field categories" exist:
      | name |
      | Category for test |
    And I log in as "admin"
    And I navigate to "Courses > Course custom fields" in site administration

  Scenario: Create a custom course field
    When I click on "Add a new custom field" "link"
     And I click on "Text field" "link"
     And I set the following fields to these values:
       | Name | Test field |
       | Short name | testfield |
     And I press "Save changes"
    Then I should see "Test field" in the "#customfield_catlist" "css_element"

  Scenario: Edit a custom course field
    When I click on "Add a new custom field" "link"
     And I click on "Text field" "link"
     And I set the following fields to these values:
       | Name | Test field |
       | Short name | testfield |
     And I press "Save changes"
     And I click on "[data-role='editfield']" "css_element"
     And I set the following fields to these values:
       | Name | Edited field |
     And I press "Save changes"
    Then I should see "Edited field" in the "#customfield_catlist" "css_element"

  @javascript
  Scenario: Delete a custom course field
    When I click on "Add a new custom field" "link"
     And I click on "Text field" "link"
     And I set the following fields to these values:
       | Name       | Test field |
       | Short name | testfield |
     And I press "Save changes"
     And I click on "[data-role='deletefield']" "css_element"
     And I click on "Yes" "button" in the "Delete" "dialogue"
     And I wait until the page is ready
     And I wait until "Test field" "text" does not exist
    Then I should not see "Test field" in the "#customfield_catlist" "css_element"

  Scenario: A text field with a link setting must show link on course listing
    Given the following "users" exist:
      | username | firstname | lastname  | email |
      | teacher1 | Teacher   | Example 1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
      | user     | course | role |
      | teacher1 | C1     | editingteacher |
     And I navigate to "Courses > Course custom fields" in site administration
     And I click on "Add a new custom field" "link"
     And I click on "Text field" "link"
     And I set the following fields to these values:
       | Name       | See more on website |
       | Short name | testfield |
       | Visible to | Everyone |
       | Link       | https://www.moodle.org/$$ |
     And I press "Save changes"
     And I log out
    Then I log in as "teacher1"
     And I am on "Course 1" course homepage
     And I navigate to "Edit settings" in current page administration
     And I set the following fields to these values:
       | See more on website | course/view.php?id=35 |
     And I press "Save and display"
     And I am on site homepage
    Then I should see "See more on website" in the ".customfields-container a" "css_element"
