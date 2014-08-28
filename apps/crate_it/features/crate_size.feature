@javascript
Feature: Crate Size Update
  As a user
  I want to see the size of the contents of my crate

    Background:
    Given I have no crates
    And I have no files
    And I have these files within the root folder
    | filename  | size_in_bytes | 
    | file1.txt | 150           |
    | file2.txt | 200           |
    | file3.txt | 1024          |
    | file4.txt | 8192          |
    | file5.txt | 1048576       |
    And I'm logged in to ownCloud as "test"

    #CRATEIT-205
    Scenario: Crate size updates when adding and removing files
      When I add "file1.txt" to the current crate
      Then I go to the crate_it page
      Then the selected crate should have size "150 B"
      And I go to the files page
      And I add "file2.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "350 B"
      And I go to the files page
      And I add "file3.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "1.3 kB"
      And I go to the files page
      And I add "file4.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "9.3 kB"
      And I go to the files page
      And I add "file5.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "1 MB"
      #duplicate file
      And I go to the files page
      And I add "file5.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "1 MB"
      And I remove "file3.txt" 
      Then I press "Remove" on the popup dialog
      # size rounded up, still unchanged
      Then the selected crate should have size "1 MB" 
      And I remove "file5.txt" from the file system
      # size should be unchanged without refresh
      Then the selected crate should have size "1 MB"
      And I go to the crate_it page
      Then the selected crate should have size "8.3 kB"
      And I remove "file2.txt"
      Then I press "Remove" on the popup dialog
      Then the selected crate should have size "8.1 kB"
      And I remove "file4.txt"
      Then I press "Remove" on the popup dialog
      Then the selected crate should have size "150 B"
      And I go to the files page
      And I add "file4.txt" to the current crate
      And I go to the crate_it page
      Then the selected crate should have size "8.1 kB"
      # clear the crate
      And I clear the crate
      And I press "Clear" on the popup dialog
      Then the selected crate should have size "0 B"
   
        
      



