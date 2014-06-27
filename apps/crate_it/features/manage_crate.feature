@javascript
Feature: Manage the items in a crate (CRUD)
  As a user
  I want to create virtual folders, rename and remove items

  Background:
    Given I have no crates
    And I have no files
    And I have file "file.txt" within the root folder
    And I'm logged in to ownCloud as "test"
    When I add "file.txt" to the current crate
    Then I go to the crate_it page

  #CRATEIT-48
  Scenario: Crate items display the correct crate item actions
    Given I have folder "folder1"
    When I go to the files page
    Then I add "folder1" to the current crate
    And I go to the crate_it page
    Then I should have crate actions "Rename Item, Remove Item" for "file.txt"
    And I should not have crate actions "Add" for "file.txt"

  #CRATEIT-48
  Scenario: Crate state is maintained when navigating between file and crate views
    Given I have folders "folder1/folder2"
    And I go to the files page
    When I add "folder1" to the current crate
    Then I go to the crate_it page
    When I toggle expand on "folder1"
    Then "folder2" should be visible in the current crate
    When I go to the files page
    Then I go to the crate_it page
    Then "folder2" should be visible in the current crate

  #CRATEIT-106
  Scenario: A user can remove an item from their crate
    When I remove "file.txt"
    Then I should see "Remove item 'file.txt' from crate?"
    When I press "Remove" on the popup dialog
    Then I should see "file.txt removed"
    And "file.txt" should not be in the crate

  #CRATEIT-106
  Scenario: A user can cancel the remove action
    When I remove "file.txt"
    Then I press "Cancel" on the popup dialog
    Then "file.txt" should be in the crate

  #CRATEIT-106
  Scenario: Removing a folder also removes any contents of that folder
    Given I have folder "folder1"
    And I have file "file2.txt" within "/folder1"
    When I go to the files page
    And I add "folder1" to the current crate
    Then I go to the crate_it page
    When I remove "folder1"
    Then I press "Remove" on the popup dialog
    Then "folder1" should not be in the crate
    And "file2.txt" should not be in the crate

  #CRATEIT-106
  Scenario: A user can rename an item in their crate
    When I rename "file.txt"
    Then the "rename-item" field should contain "file.txt"
    Then I fill in "rename-item" with "newname.txt"
    When I press "Rename" on the popup dialog
    Then I should see "Renamed file.txt to newname.txt"
    Then "file.txt" should not be in the crate
    And "newname.txt" should be in the crate

  #CRATEIT-106
  Scenario: A user can cancel renaming an item in their crate
    When I rename "file.txt"
    Then I press "Cancel" on the popup dialog
    Then "file.txt" should be in the crate

  #CRATEIT-107
  Scenario: A user can add virtual folders to their crate
    When I add a virtual folder to "default_crate"
    Then I should see "Add Folder"
    When I fill in "add-folder" with "Virtual Folder"
    Then I press "Add" on the popup dialog
    Then I should see "Virtual Folder added"
    Then "Virtual Folder" should be visible in the current crate

  #CRATEIT-107
  Scenario: A user can cancel adding virtual folder to their crate
    When I add a virtual folder to "default_crate"
    Then I should see "Add Folder"
    When I fill in "add-folder" with "Virtual Folder"
    Then I press "Cancel" on the popup dialog
    Then "Virtual Folder" should not be in the crate






