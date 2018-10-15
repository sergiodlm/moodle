@core @core_course @course_customfield
Feature: Managers can manage categories for course custom fields
  In order to have additional data on the course
  As a manager
  I need to create, edit, remove and sort custom field's categories

  Scenario: Create a category for custom course fields
    Given I log in as "admin"
    When I navigate to "Courses > Course custom fields" in site administration
    And I press "Add a new category"
    And I wait until the page is ready
    Then I should see "Other fields" in the "#customfield_catlist" "css_element"

  @javascript
  Scenario: Edit a category name for custom course fields
    Given the following "course custom field categories" exist:
      | name |
      | Category for test |
    And I log in as "admin"
    And I navigate to "Courses > Course custom fields" in site administration
    And I click on "Edit category name" "link" in the "//div[contains(@class,'categoryinstance') and contains(.,'Category for test')]" "xpath_element"
    And I set the field "New value for Category for test" to "Good fields"
    And I press key "13" in the field "New value for Category for test"
    Then I should not see "Category for test" in the "#customfield_catlist" "css_element"
    And "New value for Category for test" "field" should not exist
    And I should see "Good fields" in the "#customfield_catlist" "css_element"

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
