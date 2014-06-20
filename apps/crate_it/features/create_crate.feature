@javascript
Feature: Create and select a new crate
  As a user
  I want to create and select a new crate as my current crate

  Background:
    Given I have no crates
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page

  #CRATEIT-45
  Scenario: When a user creates a new crate, it is selected by default
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    And I fill in "New Cr8 Description" with "crate description"
    Then I press "Create" on the popup dialog
    #Then I should see notice "Crate new crate successfully created"
    And the selected crate should be "new crate"
    And I should see the crate description "crate description"
    
  #CRATEIT-45
  Scenario: Creating a new crate that is not unique results in an error
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    Then I press "Create" on the popup dialog
    And I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    And I press "Create" on the popup dialog
    Then I should see error 'Crate with name "new crate" already exists' in the modal

  #CRATEIT-45
  Scenario: Ensure the crate name field is mandatory
    When I click the new crate button
    And I press "Create" on the popup dialog
    Then I should see a "New Cr8 Name" validation error "This field is mandatory"

  #CRATEIT-45
  Scenario: Ensure the crate name field has a max length of 128
    When I click the new crate button
    Then I fill in "crate_input_name" with a long string of 129 characters
    And I press "Create" on the popup dialog
    And the selected crate name should be a long string truncated to 128 characters
  
  #CRATEIT-45
  Scenario: Ensure the crate description field has a max of 8000
    When I click the new crate button
    And I fill in "New Cr8 Name" with "crate_with_long_desc"
    Then I fill in "crate_input_description" with a long string of 8001 characters
    And I press "Create" on the popup dialog
    And the selected crate description should be a long string truncated to 8000 characters

  #CRATEIT-45
  Scenario: Cancel creating a crate
    When I click the new crate button
    Then I fill in "New Cr8 Name" with "another crate"
    And I press "Cancel" on the popup dialog
    Then I should not see "another crate"
    And I should not have crate "another crate"
   
  #CRATEIT-45
  Scenario: Validation Errors get cleared after modal disappear
    When I click the new crate button
    And I press "Create" on the popup dialog
    And I press "Cancel" on the popup dialog
    And I click the new crate button
    Then the create crate modal should be clear of input and errors
    
  #CRATEIT-45
  Scenario: Fields get cleared after modal disappear
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    And I fill in "New Cr8 Description" with "some description"
    And I press "Cancel" on the popup dialog
    And I click the new crate button
    Then the create crate modal should be clear of input and errors
  
  #CRATEIT-45
  Scenario: Error message gets cleared after modal disappear
    When I click the new crate button
    And I fill in "New Cr8 Name" with "default_crate"
    And I press "Create" on the popup dialog
    And I press "Cancel" on the popup dialog
	  And I click the new crate button
    Then the create crate modal should be clear of input and errors
  
