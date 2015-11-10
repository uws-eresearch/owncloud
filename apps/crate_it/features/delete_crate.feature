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
    And I fill in "New Crate Name" with "crate1"
    And I press "Create" on the popup dialog
    And I go to the files page
    

    # CRATEIT-78
    Scenario: A user can delete a crate
      And I add "file.txt" to the current crate
      Then I go to the crate_it page
      When I click the delete crate button
      Then I should see "Crate crate1 has items, proceed with deletion?" in the modal
      And I press "Delete" on the popup dialog
      # Then I should see notice "Crate crate1 deleted" // TODO: This doesn't work some unknown reason
      Then I should not have crate "crate1"
      
    # CRATEIT-236
    Scenario: User gets prompted appropriate messages when crate has items and/or metadata
      And I add "file.txt" to the current crate
      Then I go to the crate_it page
      # Add description
      When I click the edit description button
      And I fill in "crate_description" with "desc"
      And I click the Save button
      
      # Add creator
      Then I click to wrap Creators
      Then I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      And I press "Add" on the popup dialog
      
      # Add grant     
      When I expand the creator metadata section 
      When I expand the grant number metadata section
      Then I click on "add-activity"
      And I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      And I press "Add" on the popup dialog
      
      # Try delete
      Then I click the delete crate button
      And I should see "Crate crate1 has items and metadata, proceed with deletion?" in the modal
      And I press "Cancel" on the popup dialog
      
      # Remove item
      When I remove "file.txt"
      Then I should see "Remove item 'file.txt' from crate?"
      When I press "Remove" on the popup dialog
      
       # Try delete
      And I click the delete crate button
      Then I should see "Crate crate1 has metadata, proceed with deletion?" in the modal
      And I press "Cancel" on the popup dialog
      
      # Remove creator
      When I expand the creator metadata section
      When I remove creator "joe@bloggs.org" from the selected list
      
       # Try delete
      And I click the delete crate button
      # there is still metadata
      Then I should see "Crate crate1 has metadata, proceed with deletion?" in the modal
      And I press "Cancel" on the popup dialog
      
      # Remove grant
      When I expand the creator metadata section
      And I expand the grant number metadata section
      And I remove grant "123123" from the selected list
      
      # Try delete
      And I click the delete crate button
      # there is still description metadata
      Then I should see "Crate crate1 has metadata, proceed with deletion?" in the modal
      And I press "Cancel" on the popup dialog
      
      # Remove description
      When I expand the description metadata section
      When I click the edit description button
      And I fill in "crate_description" with ""
      And I click the Save button
      
      # Try delete
      And I wait for 3 seconds
      And I click the delete crate button
      # Then I should see notice "Crate crate1 deleted" // TODO: This doesn't work some unknown reason
      And I wait for 5 seconds
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
      Then I should not see "Crate crate1 has items, proceed with deletion?"
      #Then I should see "Crate crate1 deleted"

