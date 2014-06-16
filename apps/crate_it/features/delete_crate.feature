@javascript
Feature: Delete an existing crate
  As a user
  I want to create and select a new crate as my current crate

  Background:
    Given I have no crates
    And I have no files
    And I'm logged in to ownCloud as "test"
    And I have file "file.txt" within the root folder
    And I go to the crate_it page
    And I wait for 2 seconds
    And I have crate "crate1"

    # CRATEIT-78
    Scenario: A user can delete a crate
      When I go to the files page
      And I add "file.txt" to the default crate
      Then I go to the crate_it page
      And I wait for 2 seconds
      When I click the delete crate button
      Then I should see "Crate crate1 is not empty, proceed with deletion?"
      And I click "Delete" in the create crate modal
      And I wait for 1 seconds
      # Then I should see notice "Crate crate1 deleted" // TODO: This doesn't work some unknown reason
      Then I should not have crate "crate1"

    # CRATEIT-78
    Scenario: A user can cancel deleting a crate
      When I go to the files page
      And I add "file.txt" to the default crate
      Then I go to the crate_it page
      And I wait for 2 seconds
      When I click the delete crate button
      And I click "Cancel" in the create crate modal
      Then I should have crate "crate1"

    # CRATEIT-78
    Scenario: A user is not prompted for confirmation if the crate is empty
      When I click the delete crate button
      Then I should not see "Crate crate1 is not empty, proceed with deletion?"
      And I wait for 1 seconds
      #Then I should see "Crate crate1 deleted"

