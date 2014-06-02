@javascript
Feature: Clear crate contents
  As a user
  I want clear the contents of my crate while preserving the metadata

    Background:
    Given I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I delete the default crate
    And I go to the files page
    And I have no files
    And I have file "file.txt" within the root folder
    When I add "file.txt" to the default crate
    Then I go to the crate_it page
    And I wait for 2 seconds

    #CRATEIT-77
    Scenario: Clear the contents of a crate
      When I follow "Clear"
      Then I should see "All items will be removed from this Crate, Continue?"
      When I press "Clear" on the popup dialog
      And I wait for 1 seconds
      Then I should see "Crate deafult_crate has been cleared"
      And the crate should be empty

    #CRATEIT-77
    Scenario: Cancel clear the contents of a crate
      When I follow "Clear"
      Then I press "Cancel" on the popup dialog
      And I wait for 1 seconds
      Then "file.txt" should be in the crate



