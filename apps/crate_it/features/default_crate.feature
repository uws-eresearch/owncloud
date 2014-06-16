@javascript
Feature: Default crate always exists
  As a user
  I want to create a crate that holds files and folders

  Background:
    Given I have no crates
    And I have no files
    And I'm logged in to ownCloud as "test"
    When I go to the crate_it page

  #CRATEIT-49
  Scenario: Default crate automatically created at startup
    Then I should see the default crate already created and selected
  
  #CRATEIT-49
  Scenario: When I delete the default crate, a new one is created
    When I click the delete crate button
    And I wait for 1 seconds
    Then I should see the default crate already created and selected

