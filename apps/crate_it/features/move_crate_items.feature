@javascript
Feature: Add files and folders to a crate
  As a user
  I want to add files and folders to crate

  Background:
    Given I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I delete the default crate
    Then I should see the default crate already created and selected
    And I go to the files page
    And I have no files
    And I have file "file.txt" within the root folder
    And I have folder "folder1" within the root folder
    And I have folder "folder2" within the root folder
    And I have file "file2.txt" within "folder2"
    When I add "file.txt" to the default crate
    When I add "folder1" to the default crate
    When I add "folder2" to the default crate
    Then I go to the crate_it page
    Then I wait for 2 seconds

  # CRATEIT-101
  Scenario: A user can drag a file into a folder
    When I drag "file.txt" and drop it on "folder1"
    And I wait for 1 seconds
    Then I should see "Item file.txt moved"
    And the default crate should contain "file.txt" within "folder1"

  # CRATEIT-101
  Scenario: A user can drag a folder into another folder
    When I drag "folder2" and drop it on "folder1"
    And I wait for 1 seconds
    Then I should see "Item folder2 moved"
    And the default crate should contain "folder2" within "folder1"
    And "file2.txt" should not be visible in the default crate

  # CRATEIT-101
  Scenario: A user can drag an item into a folder of another folder
    When I toggle expand on "folder1"
    And I wait for 1 seconds
    And I toggle expand on "folder2"
    When I drag "file2.txt" and drop it on "folder1"
    And the default crate should contain "file2.txt" within "folder1"

