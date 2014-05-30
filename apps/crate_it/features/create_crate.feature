@javascript
Feature: Create and select a new crate
  As a user
  I want to create and select a new crate as my current crate

  Background:
    Given I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    #And I delete all existing crates

  #CRATEIT-45
  Scenario: When a user creates a new crate, it is selected by default
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    Then I click "Create" in the create crate modal
    Then I should see notice "Crate new crate successfully created"
    Then I wait for 3 seconds
    And the selected crate should be "new crate"
    
  #CRATEIT-45
  Scenario: Creating a new crate that is not unique results in an error
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    Then I click "Create" in the create crate modal
    Then I wait for 4 seconds
    And I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    And I click "Create" in the create crate modal
    Then I should see error 'Crate with name "new crate" already exists' in the modal

  #CRATEIT-45
  Scenario: Ensure the crate name field is mandatory
    When I click the new crate button
    And I click "Create" in the create crate modal
    Then I should see a "New Cr8 Name" validation error "This field is mandatory" 

  #CRATEIT-45
  Scenario: Ensure the crate name field has a max length of 128
    When I click the new crate button
    Then I fill in "New Cr8 Name" with a long string
    And I click "Create" in the create crate modal
    And the selected crate should be a long string truncated to 128 characters

  #CRATEIT-45
  Scenario: Cancel creating a crate
    When I click the new crate button
    Then I fill in "New Cr8 Name" with "new crate"
    And I click "Cancel" in the create crate modal
    Then I should not see "new crate"
    And I should not have crate "new crate"

