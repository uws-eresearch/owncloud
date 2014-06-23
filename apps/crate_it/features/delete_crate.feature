@javascript
Feature: Delete an existing crate
  As a user
  I want to create and select a new crate as my current crate

  Background:
    Given I have no crates
    And I have no files
    And I have file "file.txt" within the root folder
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    When I click the new crate button
    And I fill in "New Cr8 Name" with "crate1"
    And I press "Create" on the popup dialog
    And I go to the files page
    

    # CRATEIT-78
    Scenario: A user can delete a crate
      And I add "file.txt" to the current crate
      Then I go to the crate_it page
      When I click the delete crate button
      Then I should see "Crate crate1 is not empty, proceed with deletion?"
      And I press "Delete" on the popup dialog
      # Then I should see notice "Crate crate1 deleted" // TODO: This doesn't work some unknown reason
      Then I should not have crate "crate1"

    # CRATEIT-78
    Scenario: A user can cancel deleting a crate
      And I add "file.txt" to the current crate
      Then I go to the crate_it page
      When I click the delete crate button
      And I press "Cancel" on the popup dialog
      Then I should have crate "crate1"

    # CRATEIT-78
    Scenario: A user is not prompted for confirmation if the crate is empty
      When I go to the crate_it page
      And I click the delete crate button
      Then I should not see "Crate crate1 is not empty, proceed with deletion?"
      #Then I should see "Crate crate1 deleted"

