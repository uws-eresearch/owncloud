@javascript
Feature:Check crate
  As a user
  I want to check if the files/folders in the crate are consistent

  Background:
    Given I have no crates
    And I have no files
    And I have folders "folder1/folder2"
    And I have file "file0.txt" within the root folder
    And I have file "file1.txt" within the root folder
    And I have file "file2.txt" within "folder1/folder2"
    And I have crate "crate1"
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I select crate "crate1"
    And I go to the files page

  #CRATEIT-58
  Scenario: Check crate with all valid items
    Given I add "file1.txt" to the current crate
    And I add "folder1" to the current crate
    And I go to the crate_it page
    And I click on "check"
    Then I should see "All items are valid." in the modal
    And I click on "confirm_checker"
    And I wait for 3 seconds
    Then I should see green ticks next to these items
    | filename   |
    | folder1    |
    | file1.txt  |
    
  #CRATEIT-58
  Scenario: Check crate with some invalid items
    Given I add "file1.txt" to the current crate   
    And I add "file0.txt" to the current crate
    And I add "folder1" to the current crate
    And I go to the crate_it page
    And I remove "file1.txt" from the file system
    And I rename "folder1" to "newfolder" in the file system
    And I click on "check"
    Then I should see "The following items no longer exist:"
    And I should see these files listed as invalid
    | filename  |
    | file1.txt |
    | file2.txt |
    Then I click on "confirm_checker"
    Then I should see green ticks next to these items
    | filename  |
    | file0.txt |    
    Then I should see red crosses next to these items
    | filename  |
    | file1.txt |
    | file2.txt |    
    And I remove "file1.txt" 
    And I press "Remove" on the popup dialog
    And I click on "check"
    Then I should see "The following item no longer exists:"
    And I should see these files listed as invalid
    | filename  |
    | file2.txt |
    Then I click on "confirm_checker"
    Then I should see green ticks next to these items
    | filename  |
    | file0.txt |   
    Then I should see red crosses next to these items
    | filename  |
    | file2.txt |        
    And I rename "file0.txt" to "file0" in the file system
    And I click on "check"
    Then I should see "The following items no longer exist:"
    And I should see these files listed as invalid
    | filename  |
    | file0.txt |
    | file2.txt |
    Then I click on "confirm_checker"
    Then I should see red crosses next to these items
    | filename  |
    | file0.txt |
    | file2.txt |          
    
  #CRATEIT-193
  Scenario: Test deeply nested folder structure
    Given I have folders "folder1/folder2/folder3/folder4"
    And I have file "fileA.txt" within "folder1/folder2/folder3"
    And I have file "fileB.txt" within "folder1/folder2/folder3"
    And I have file "fileC.txt" within "folder1/folder2/folder3/folder4"
    And I have file "fileD.txt" within "folder1/folder2/folder3/folder4"
    And I have file "fileE.txt" within "folder1/folder2/folder3/folder4"
    And I have file "fileF.txt" within "folder1/folder2/folder3/folder4"
    And I add "file0.txt" to the current crate
    And I add "file1.txt" to the current crate
    And I add "folder1" to the current crate
    And I go to the crate_it page
    And I remove "folder1/folder2/folder3/folder4/fileD.txt" from the file system
    And I click on "check"
    Then I should see "The following item no longer exists:"
    And I should see these files listed as invalid
    | filename  |
    | fileD.txt |
    Then I click on "confirm_checker"
    And I should see red crosses next to these items
    | filename |
    | folder1  |
    | folder2  |
    | folder3  |
    | folder4  |
    | fileD.txt|
    | crate1   |
    And I should see green ticks next to these items
    | filename  |
    | file0.txt |
    | file1.txt |
    | file2.txt |
    | fileA.txt |
    | fileB.txt |
    | fileC.txt |
    | fileE.txt |
    | fileF.txt |
    And I clear the crate
    And I press "Clear" on the popup dialog
    And I click on "check"
    Then I should see "All items are valid." in the modal
    And I click on "confirm_checker"
    Then I should see green ticks next to these items
    | filename |
    | crate1   |
    
  #CRATEIT-222
  Scenario: Empty folders should be valid 
    Given I add "folder1" to the current crate
    And I go to the crate_it page    
    And I remove "folder1/folder2/file2.txt" from the file system
    And I click on "check"
    Then I should see these files listed as invalid
    | filename |
    | file2.txt |
    Then I click on "confirm_checker"
    And I should see red crosses next to these items
    | filename  |
    | file2.txt |
    | folder1   |
    | folder2   |
    | crate1    | 
    And I expand 'folder1'
    And I expand 'folder2'
    Then I remove "file2.txt"
    And I press "Remove" on the popup dialog
    And I click on "check"
    Then I should see "All items are valid." in the modal
    And I click on "confirm_checker"
    Then I should see green ticks next to these items
    | filename  |
    | folder1   |
    | folder2   |
    | crate1    | 
    
  
  
  
    