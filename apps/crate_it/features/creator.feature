@javascript
Feature: Search, add and remove grant number 
  As a user
  I want to add grant number related to the crate
  
  Background:
    Given I have no crates
    And I have crate "crate1"
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    And I select crate "crate1"
    
  #CRATEIT-80
  Scenario: Creator lookup
    Given I fill in "keyword_creator" with "John"
    And I click the search creator button
    Then I should see these entries in the creator result list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr John Doe     | john@doe.org        |
      | Mr Daniel Johns | dan@silverchair.com |
      
  #CRATEIT-80
  Scenario: Add and Remove creators
    Given I fill in "keyword_creator" with "John"
    And I click the search creator button
    And I add creator "1" to the list
    And I add creator "3" to the list
    Then I should see these entries in the selected creatora list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr Daniel Johns | dan@silverchair.com |
    And I remove creator "1" in the list
    Then I should see these entries in the selected creatora list
      | name            | email               |
      | Mr Daniel Johns | dan@silverchair.com |
    And I add creator "2" to the list
    Then I should see these entries in the selected creatora list
      | name            | email               |
      | Mr Daniel Johns | dan@silverchair.com |
      | Mr John Doe     | john@doe.org        |
      
   #CRATEIT-80
   Scenario: Creator lookup result should exclude selected numbers
     Given I fill in "keyword_creator" with "John"
     And I click the search creator button 
     And I add creator "2" to the list
     And I click the search creator button
     Then I should see these entries in the creator result list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr Daniel Johns | dan@silverchair.com |
     
   #CRATEIT-80
   Scenario: Server returns no results should trigger a notification
     Given I fill in "keyword_creator" with "John"
     When I click the search creator button and get no results
     Then I should see "0 new results returned"
     
   #CRATEIT-80
   Scenario: Click on 'Clear All' should remove all selected creators
     Given I fill in "keyword_creator" with "John"
     And I click the search creator button 
     And I add creator "1" to the list
     And I add creator "3" to the list
     When I clear all creators
     Then I should see "Clear all creators?"
     When I press "Clear" on the popup dialog
     Then I should have no selected creators
     
   #CRATEIT-80
   Scenario: A user can cancel clearing all creators
     Given I fill in "keyword_creator" with "John"
     And I click the search creator button 
     And I add creator "1" to the list
     And I add creator "3" to the list
     When I clear all creators
     Then I should see "Clear all creators?"
     When I press "Cancel" on the popup dialog
     Then I should see these entries in the selected creatora list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr Daniel Johns | dan@silverchair.com |
      
    
    
      