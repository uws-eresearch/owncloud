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
    And I expand the grant number metadata section
    
  #CRATEIT-161
  Scenario: Grant number lookup
    Given I fill in "keyword_activity" with "123"
    And I click the search grant number button 
    Then I should see these entries in the result list
      | grant    | year | title   |
      | 111123   | 1999 | Title A |          
      | 123123   | 2010 | Title B |
      | 123456   | 1988 | Title C |
      
  #CRATEIT-161
  Scenario: Add and Remove Grant Numbers
    Given I fill in "keyword_activity" with "123"
    And I click the search grant number button
    And I add grant number "111123" to the list
    And I add grant number "123456" to the list
    Then I should see these entries in the selected grant number list
      | grant    | year | title   |
      | 111123   | 1999 | Title A |    
      | 123456   | 1988 | Title C |
    And I remove grant number "123456" in the list
    Then I should see these entries in the selected grant number list
      | grant    | year | title   |
      | 111123   | 1999 | Title A |    
    And I add grant number "123123" to the list
    Then I should see these entries in the selected grant number list
      | grant    | year | title   |
      | 111123   | 1999 | Title A |  
      | 123123   | 2010 | Title B |  
      
   #CRATEIT-162
   Scenario: Grant number lookup result should exclude selected numbers
     Given I fill in "keyword_activity" with "123"
     And I click the search grant number button 
     And I add grant number "123123" to the list
     And I click the search grant number button
     Then I should see these entries in the result list
      | grant    | year | title   |
      | 111123   | 1999 | Title A |    
      | 123456   | 1988 | Title C |
   
   #CRATEIT-155
   @wip
   Scenario: Mint server unavailable should trigger a notification
     Given the mint server became unavailable
     When I fill in 'keyword_activity' with '123'
     And I click the 'Search Grant Number' button
     Then I should see 'Server unreachable. Please try again later.'
     
   #CRATEIT-162
   Scenario: Server returns no results should trigger a notification
     Given I fill in "keyword_activity" with "123"
     When I click the search grant number button and get no results
     Then I should see "0 new results returned"
     
   #CRATEIT-162
   Scenario: Click on 'Clear All' should remove all selected grant numbers
     Given I fill in "keyword_activity" with "123"
     And I click the search grant number button 
     And I add grant number "123123" to the list
     And I add grant number "111123" to the list
     When I clear all activities
     Then I should see "Clear all grants?"
     When I press "Clear" on the popup dialog
     Then I should no selected grants
    
   #CRATEIT-162
   Scenario: A user can cancel clearing all grant numbers
     Given I fill in "keyword_activity" with "123"
     And I click the search grant number button 
     And I add grant number "123123" to the list
     And I add grant number "111123" to the list
     When I clear all activities
     Then I should see "Clear all grants?"
     When I press "Cancel" on the popup dialog
     Then I should see these entries in the selected grant number list
      | grant    | year | title   |
      | 123123   | 2010 | Title B |
      | 111123   | 1999 | Title A |
      
    
    
      