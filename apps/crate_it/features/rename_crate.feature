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
    Then the "rename-item" field should contain "default_crate"
    Then I fill in "rename-item" with "new crate name"
    When I press "Rename"
    And I wait for 2 seconds
    # Then I should see notice "Renamed default_crate to new crate name" // TODO: Fix notification tests
    And the crate should have name "new crate name"


  #CRATEIT-127
  Scenario: A user can cancel renaming a crate  
    When I rename "default_crate"
    Then I press "Cancel" on the popup dialog
    And the crate should have name "default_crate"