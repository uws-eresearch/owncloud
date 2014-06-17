@javascript
Feature: Manage the items in a crate (CRUD)
  As a user
  I want to create virtual folders, rename and remove items

  Background:
    Given I have no crates
    And I have no files
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I have file "file.txt" within the root folder
    When I add "file.txt" to the default crate
    Then I go to the crate_it page
    And I wait for 2 seconds

  #CRATEIT-48
  Scenario: Crate items display the correct crate item actions
    When I go to the files page
    And I have folder "folder1" within the root folder
    Then I add "folder1" to the default crate
    And I go to the crate_it page
    And I wait for 2 seconds
    Then I should have crate actions "Rename Item, Remove Item" for "file.txt"
    And I should not have crate actions "Add" for "file.txt"

  #CRATEIT-48
  Scenario: Crate state is maintained when navigating between file and crate views
    When I go to the files page
    Then I have folder "folder1" within the root folder
    And I have folder "folder2" within "folder1"
    Then I add "folder1" to the default crate
    Then I go to the crate_it page
    And I wait for 2 seconds
    When I toggle expand on "folder1"
    And I wait for 1 seconds
    Then "folder2" should be visible in the default crate
    When I go to the files page
    Then I go to the crate_it page
    And I wait for 2 seconds
    Then "folder2" should be visible in the default crate


  #CRATEIT-106
  Scenario: A user can remove an item from their crate
    When I remove "file.txt"
    Then I should see "Remove item 'file.txt' from crate?"
    When I press "Remove"
    And I wait for 1 seconds
    Then I should see "file.txt removed"
    And "file.txt" should not be in the crate

  #CRATEIT-106
  Scenario: A user can cancel the remove action
    When I remove "file.txt"
    Then I press "Cancel" on the popup dialog
    Then "file.txt" should be in the crate

  #CRATEIT-106
  Scenario: Removing a folder also removes any contents of that folder
    When I go to the files page
    And I have folder "folder1" within the root folder
    And I have file "file2.txt" within "/folder1"
    Then I add "folder1" to the default crate
    And I go to the crate_it page
    And I wait for 2 seconds
    When I remove "folder1"
    Then I press "Remove"
    Then "folder1" should not be in the crate
    And "file2.txt" should not be in the crate

  #CRATEIT-106
  Scenario: A user can rename an item in their crate
    When I rename "file.txt"
    Then the "rename-item" field should contain "file.txt"
    Then I fill in "rename-item" with "newname.txt"
    When I press "Rename"
    And I wait for 1 seconds
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
    When I add a virtual folder to "/"
    Then I should see "Add Folder"
    When I fill in "add-folder" with "Virtual Folder"
    Then I press "Add"
    And I wait for 1 seconds
    Then I should see "Virtual Folder added"
    Then "Virtual Folder" should be visible in the default crate

  #CRATEIT-107
  Scenario: A user can cancel adding virtual folder to their crate
    When I add a virtual folder to "/"
    Then I should see "Add Folder"
    When I fill in "add-folder" with "Virtual Folder"
    Then I press "Cancel" on the popup dialog
    And I wait for 1 seconds
    Then "Virtual Folder" should not be in the crate


  #CRATEIT-126
  #Scenario: A user can not rename an item in a crate unless the name is valid
  #  When I rename file "file.txt"
  #  Then the "Rename" button should be disabled
  # TODO: Need rules of what constitutes a valid name





