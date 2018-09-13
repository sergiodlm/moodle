@core @core_course
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
    When I select "Text Area" from the "Add a new custom field" singleselect
    And I set the following fields to these values:
      | Name | Test field |
      | Short Name | testfield |
    And I press "Save changes"
    Then I should see "Test field" in the "#customfield_catlist" "css_element"

  Scenario: Edit a custom course field
    When I select "Text Area" from the "Add a new custom field" singleselect
    And I set the following fields to these values:
      | Name | Test field |
      | Short Name | testfield |
    And I press "Save changes"
    And I click on "[data-role='editfield']" "css_element"
    And I set the following fields to these values:
      | Name | Edited field |
    And I press "Save changes"
    Then I should see "Edited field" in the "#customfield_catlist" "css_element"

  @javascript
  Scenario: Delete a custom course field
    When I select "Text Area" from the "Add a new custom field" singleselect
    And I set the following fields to these values:
      | Name | Test field |
      | Short Name | testfield |
    And I press "Save changes"
    And I click on "[data-role='deletefield']" "css_element"
    And I click on "Yes" "button" in the "Delete" "dialogue"
    And I wait until the page is ready
    And I wait until "Test field" "text" does not exist
    Then I should not see "Test field" in the "#customfield_catlist" "css_element"
