@javascript
Feature: Delete an existing crate
  As a user
  I want to create and select a new crate as my current crate

  Background:
    Given I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I wait for 2 seconds
    And I have crate "crate1"
    #And I delete all existing crates

    # CRATEIT-78
    Scenario: A user can delete a crate
      When I click the delete crate button
      Then I should see "Crate crate1 is not empty, proceed with deletion?"
      And I click "Delete" in the create crate modal
      And I wait for 1 seconds
      Then I should see "Crate crate1 deleted"
      Then I should not have crate "crate1"

    # CRATEIT-78
    Scenario: A user can cancel deleting a crate
      When I click the delete crate button
      And I click "Cancel" in the create crate modal
      Then I should have crate "crate1"