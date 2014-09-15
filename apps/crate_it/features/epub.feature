@javascript
Feature: Download ePub
  As a user
  I want to view the contents of my crate as an ePub

  Background:
    Given I have no crates
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page

  #CRATEIT-124
  Scenario: A user can see the download ePub button
    When I click the download button
    Then I should see "ePub"