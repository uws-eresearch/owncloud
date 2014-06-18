@javascript
Feature: Add files and folders to a crate
  As a user
  I want to add files and folders to crate

  Background:
    Given I have no crates
    And I have no files
    And I have folders "folder1/folder2"
    And I have file "file.txt" within the root folder
    And I have file "file2.txt" within "folder1/folder2"
    And I'm logged in to ownCloud as "test"

  #CRATEIT-46
  Scenario: A user can add a file to a crate
    When I add "file.txt" to the default crate
    Then I go to the crate_it page
    Then I wait for 2 seconds
    Then the default crate should contain "file.txt" within the root folder
  
  #CRATEIT-46
  Scenario: A user can add a folder to a crate preserving its structure
    When I add "folder1" to the default crate
    Then I go to the crate_it page
    Then I wait for 2 seconds
    Then the default crate should contain "folder1" within the root folder
    And the default crate should contain "folder2" within "folder1"
    And the default crate should contain "file2.txt" within "folder2"

  #CRATEIT-46
  Scenario: Folders added to a crate can be expanded and collapsed
    When I add "folder1" to the default crate
    Then I go to the crate_it page
    Then I wait for 2 seconds
    Then "folder2" should not be visible in the default crate
    When I toggle expand on "folder1"
    And I wait for 1 seconds
    Then "folder2" should be visible in the default crate
    When I toggle expand on "folder1"
    And I wait for 1 seconds
    Then "folder2" should not be visible in the default crate

  #CRATEIT-46
  Scenario: A user can add a file to a crate multiple times
    When I add "file.txt" to the default crate
    Then I wait for 2 seconds
    And I add "file.txt" to the default crate
    Then I wait for 2 seconds
    When I go to the crate_it page
    #Then I wait for 3 seconds
	#FIXME Then the default crate should contain "file.txt, file.txt" within the root folder, in that order

  #CRATEIT-46
  Scenario: A user can add a folder to a crate multiple times
    When I add "folder1" to the default crate
    And I add "folder1" to the default crate
    When I go to the crate_it page
    #Then I wait for 3 seconds
	#FIXME Then the default crate should contain "folder1, folder1" within the root folder, in that order

  #CRATEIT-46
  Scenario: Files and folders are added to a crate sequentially
    When I add "file.txt" to the default crate
    And I add "folder1" to the default crate
    And I add "file.txt" to the default crate
    And I add "folder1" to the default crate
    When I go to the crate_it page
    #Then I wait for 3 seconds
	#FIXME Then the default crate should contain "file.txt, folder1, file.txt, folder1" within the root folder, in that order

  #CRATEIT-46
  Scenario: Adding a subfolder to a crate ignores parent folders
    When I navigate to folder1
    When I add "folder2" to the default crate
    And I go to the crate_it page
    Then I wait for 2 seconds
    Then the default crate should not contain "folder1" anywhere

