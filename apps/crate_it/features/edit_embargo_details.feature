@javascript
Feature: Edit embargo details
  As a user
  I want to edit the crate embargo details

  Background:
    Given I have no crates
    And I have no published crates
    And I have no redbox alerts
    And I have crate "test_embrago_details"
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I select crate "test_embrago_details"

  #CRATEIT-270
  Scenario: Check default embargo details
    When I click to wrap Embargo Details
    Then I should see embargo enabled as ""
    And I should see embargo until as ""
    And I should see embargo note as ""
    When I click the edit embargo details button
    And I wait for 2 seconds
    And I check the embargo enabled radio button
    And I click the date picker
    And I select today from the date picker
    And I fill in "embargo_details" with "Some embargo details go here"
    And I click the save embargo details button
    Then I should see embargo enabled as "Yes"
    And I should see embargo until as today
    And I should see embargo note as "Some embargo details go here"
    When I click on "publish"
    And I press "Submit" on the popup dialog
    Then redbox alerts xml file "test_embrago_details" should have field EmbargoEnabled with value "true"
    And redbox alerts xml file "test_embrago_details" should have field EmbargoDate of today
    And redbox alerts xml file "test_embrago_details" should have field EmbargoDetails with value "Some embargo details go here"

