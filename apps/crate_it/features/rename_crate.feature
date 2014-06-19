@javascript
Feature: Rename a crate from the Item Management root
  As a user
  I want to rename a crate from the Item Management root

  Background:
    Given I have no crates
    And I have no files
    And I'm logged in to ownCloud as "test"
    Then I go to the crate_it page
    And I wait for 2 seconds

  #CRATEIT-127
  Scenario: A user can rename a crate
    When I rename "default_crate"
    Then the "rename-crate" field should contain "default_crate"
    Then I fill in "rename-crate" with "new crate name"
    When I press "Rename" on the popup dialog
    And I wait for 2 seconds
    # Then I should see notice "Renamed default_crate to new crate name" // TODO: Fix notification tests
    And the crate should have name "new crate name"


  #CRATEIT-127
  Scenario: A user can cancel renaming a crate  
    When I rename "default_crate"
    Then I press "Cancel" on the popup dialog
    And the crate should have name "default_crate"

  #CRATEIT-127
  Scenario: Renaming a new crate that is not unique results in an error
    When I click the new crate button
    And I fill in "New Cr8 Name" with "new crate"
    Then I click "Create" in the create crate modal
    Then I wait for 4 seconds
    When I rename "new crate"
    Then I fill in "rename-crate" with "new crate"
    Then I should see error 'Crate with name "new crate" already exists' in the modal