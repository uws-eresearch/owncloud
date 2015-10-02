@javascript
Feature: Edit crate description
  As a user
  I want to edit the crate description

  Background:
    Given I have no crates
    And I have crate "crate1"
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I select crate "crate1"

  #CRATEIT-269
  Scenario: Check default data retention period
    And I wait for 3 seconds
    Then the selected crate should have data retention period "Perpetuity"

  #CRATEIT-269
  Scenario: Update and cancel date retention period
    And I wait for 1 seconds
    And I click to wrap Information
    And I wait for 1 seconds
    And I click to wrap Creators
    And I wait for 1 seconds
    When I click the edit data retention period button
    And I wait for 1 seconds
    And I check the radio button "1"
    And I wait for 1 seconds
    And I click the Save button for data retention period
    And I wait for 1 seconds
    Then I should see the crate data retention period as "1"

  #CRATEIT-269
  Scenario: Data retention period consistency after page reloaded
    When I click the new crate button
    And I fill in "New Crate Name" with "crate2"
    Then I press "Create" on the popup dialog
    And I select crate "crate1"
    Then the selected crate should have data retention period "Perpetuity"


