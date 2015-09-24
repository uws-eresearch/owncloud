@javascript
Feature: Add files and folders to a crate
  As a user
  I want to add files and folders to crate

  Background:
    Given I have no crates
    And I have no files
    And I have folders "folder1/folder2"
    And I have file "file.txt" within the root folder
    And I have file "\&" within the root folder
    And I have file "file2.txt" within "folder1/folder2"
    And I'm logged in to ownCloud as "test"
    And I go to the files page

  #CRATEIT-46
  Scenario: A user can add a file to a crate
    When I add "file.txt" to the current crate
    Then I go to the crate_it page
    Then the default crate should contain "file.txt" within the root folder
  
  #CRATEIT-46
  Scenario: A user can add a folder to a crate preserving its structure
    When I add "folder1" to the current crate
    Then I go to the crate_it page
    Then the default crate should contain "folder1" within the root folder
    And the default crate should contain "folder2" within "folder1"
    And the default crate should contain "file2.txt" within "folder2"

  #CRATEIT-46
  Scenario: Folders added to a crate can be expanded and collapsed
    When I add "folder1" to the current crate
    Then I go to the crate_it page
    Then "folder2" should not be visible in the current crate
    When I toggle expand on "folder1"
    Then "folder2" should be visible in the current crate
    When I toggle expand on "folder1"
    Then "folder2" should not be visible in the current crate

  #CRATEIT-46
  Scenario: A user can add a file to a crate multiple times
    When I add "file.txt" to the current crate
    And I add "file.txt" to the current crate
    When I go to the crate_it page
    Then the default crate should contain "file.txt,file.txt" within the root folder, in that order

  #CRATEIT-46
  Scenario: A user can add a folder to a crate multiple times
    When I add "folder1" to the current crate
    And I add "folder1" to the current crate
    When I go to the crate_it page
    Then the default crate should contain "folder1,folder1" within the root folder, in that order

  #CRATEIT-46
  Scenario: Files and folders are added to a crate sequentially
    When I add "file.txt" to the current crate
    And I add "folder1" to the current crate
    And I add "file.txt" to the current crate
    And I add "folder1" to the current crate
    When I go to the crate_it page
    Then the default crate should contain "file.txt,folder1,file.txt,folder1" within the root folder, in that order

  #CRATEIT-46
  Scenario: Adding a subfolder to a crate ignores parent folders
    When I navigate to "folder1"
    When I add "folder2" to the current crate
    And I go to the crate_it page
    Then the default crate should not contain "folder1" anywhere

  #CRATEIT-209
  Scenario: A user can add a file to a crate
    When I add "&" to the current crate
    Then I go to the crate_it page
    Then the default crate should contain "&" within the root folder

  #CRATEIT-239
  Scenario: A user can add a file named _html to a crate
    When I go to the crate_it page
    And I have file "_html" within the root folder
    And I go to the files page
    When I add "_html" to the current crate
    Then I go to the crate_it page
    Then the default crate should contain "_html" within the root folder

  #CRATEIT-239
  Scenario: A user can not add a folder named _html to a crate
    When I go to the crate_it page
    And I have folders "_html"
    And I go to the files page
    When I add "_html" to the current crate
    Then I go to the crate_it page
    Then the default crate should not contain "_html" anywhere

  #CRATEIT-239
  Scenario: A nested _html folder is ignored when adding to a crate
    When I go to the crate_it page
    And I have folders "folder1/_html"
    And I go to the files page
    When I add "folder1" to the current crate
    Then I go to the crate_it page
    And I expand 'folder1'
    Then the default crate should not contain "_html" anywhere
