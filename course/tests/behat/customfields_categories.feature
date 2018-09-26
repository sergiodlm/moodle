@core @core_course
Feature: Managers can manage categories for course custom fields
  In order to have additional data on the course
  As a manager
  I need to create, edit, remove and sort custom field's categories

  Scenario: Create a category for custom course fields
    Given I log in as "admin"
    When I navigate to "Courses > Course custom fields" in site administration
    And I press "Add a new category"
    And I set the following fields to these values:
      | Category Name | Test category |
    And I press "Save changes"
    Then I should see "Test category" in the "#customfield_catlist" "css_element"

  Scenario: Edit a category for custom course fields
    Given the following "course custom field categories" exist:
      | name |
      | Category for test |
    And I log in as "admin"
    And I navigate to "Courses > Course custom fields" in site administration
    And I click on "[data-role='editcategory']" "css_element"
    And I set the following fields to these values:
      | Category Name | Edited category |
    And I press "Save changes"
    Then I should see "Edited category" in the "#customfield_catlist" "css_element"

  @javascript
  Scenario: Delete a category for custom course fields
    Given the following "course custom field categories" exist:
      | name |
      | Category for test |
    And I log in as "admin"
    And I navigate to "Courses > Course custom fields" in site administration
    And I click on "[data-role='deletecategory']" "css_element"
    And I click on "Yes" "button" in the "Delete" "dialogue"
    And I wait until the page is ready
    And I wait until "Test category" "text" does not exist
    Then I should not see "Test category" in the "#customfield_catlist" "css_element"
