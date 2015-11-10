@javascript
Feature: Clear crate contents
  As a user
  I want clear the contents of my crate while preserving the metadata

    Background:
    Given I have no crates
    And I have no files
    And I have file "file.txt" within the root folder
    And I'm logged in to ownCloud as "test"
    And I go to the files page
    When I add "file.txt" to the current crate
    Then I go to the crate_it page

    #CRATEIT-77
    Scenario: Clear the contents of a crate
      When I clear the crate
      Then I should see "All items will be removed from this Crate, Continue?"
      When I press "Clear" on the popup dialog
      Then I should see "default_crate has been cleared"
      Then "file.txt" should not be in the crate

    #CRATEIT-77
    Scenario: Cancel clear the contents of a crate
      When I clear the crate
      Then I press "Cancel" on the popup dialog
      Then "file.txt" should be in the crate



