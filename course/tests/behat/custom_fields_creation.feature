@core @core_course
Feature: Managers can create course custom fields
  In order to have additional data on the course
  As a manager
  I need to create custom fields and field's categories

  @javascript
  Scenario: Create a category for custom course fields
    Given I log in as "admin"
    When I navigate to "Courses > Course custom fields" in site administration
    And I press "Create a new Category"
    And I set the following fields to these values:
      | Category Name | Test category |
    And I press "Save changes"
    Then I should see "Test category" in the "#cfield_catlist" "css_element"
