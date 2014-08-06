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

  #CRATEIT-51
  Scenario: Edit and cancel edit description
    When I click the edit description button
    # change it once
    And I fill in "crate_description" with "New Description"
    And I click the Save button
    Then I should see the crate description "New Description"
    # change it twice
    And I click the edit description button
    And I fill in "crate_description" with "Another Description"
    And I click the Save button
    Then I should see the crate description "Another Description"
    # cancel change
    And I click the edit description button
    And I fill in "crate_description" with "Some other description"
    And I click the Cancel button
    Then I should see the crate description "Another Description"
    
  
  #CRATEIT-51
  Scenario: 6000 characters cap for description
    When I click the edit description button
    And I fill in "crate_description" with a long string of 6001 characters
    Then I should see "Crate Description has reached the limit of 6,000 characters"
    And I click the Save button
    And the selected crate description should be a long string truncated to 6000 characters
  
  