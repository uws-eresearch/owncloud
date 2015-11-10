@javascript
Feature: Rename a crate from the Item Management root
  As a user
  I want to rename a crate from the Item Management root

  Background:
    Given I have no crates
    And I have no files
    And I'm logged in to ownCloud as "test"
    Then I go to the crate_it page

  #CRATEIT-127
  Scenario: A user can rename a crate
    When I rename "default_crate"
    Then the "rename-crate" field should contain "default_crate"
    Then I fill in "rename-crate" with "new crate name"
    When I press "Rename" on the popup dialog
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
    Then I fill in "New Crate Name" with "new crate"
    When I press "Create" on the popup dialog
    When I rename "new crate"
    Then I fill in "rename-crate" with "new crate"
    Then I should see error 'Crate with name "new crate" already exists' in the modal

  #CRATEIT-127
  Scenario: A crate can not have a blank name
    When I click the new crate button
    Then I fill in "New Crate Name" with "new crate"
    When I press "Create" on the popup dialog
    When I rename "new crate"
    Then I fill in "rename-crate" with "   "
    Then I should see error 'Crate name cannot be blank' in the modal

  #CRATEIT-127
  Scenario: A crate can not have a blank name
    When I click the new crate button
    Then I fill in "New Crate Name" with "new crate"
    When I press "Create" on the popup dialog
    When I rename "new crate"
    Then I fill in "rename-crate" with a long string of 129 characters
    Then I should see error 'Crate name has reached the limit of 128 characters' in the modal

  #CRATEIT-236
  Scenario: Renaming a crate with special characters doesn't break it
    When I click the new crate button
    Then I fill in "New Crate Name" with "new crate"
    When I press "Create" on the popup dialog
    When I rename "new crate"
    Then I fill in "rename-crate" with ", abc ."
    When I press "Rename" on the popup dialog
    Then the crate should have name ", abc ."
    # rename again
    Then I rename ", abc ."
    Then I fill in "rename-crate" with "......"
    # rename again
    When I press "Rename" on the popup dialog
    Then the crate should have name "......"
    When I rename "......"
    Then I fill in "rename-crate" with "?"
    Then I should see "Invalid name. Illegal characters"
    When I fill in "rename-crate" with ", abc . "
    Then I press "Rename" on the popup dialog
    Then the crate should have name ", abc ."
    
    
    
   
         