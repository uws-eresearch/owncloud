Feature: Create a crate
  As a user
  I want to create a crate that holds files and folders

  @wip
  Scenario: Default crate automatically created at startup
    Given I'm logged in to ownCloud
    When I go to the crate_it page
    Then I should see the default crate already created and selected
     
 
  