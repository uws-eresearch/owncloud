@javascript
Feature: Search, add and remove grant number 
  As a user
  I want to add grant number related to the crate
  
  Background:
    GivenI have crate "new crate"
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page
    
  #CRATEIT-161
  Scenario: Grant number lookup
    Given I fill in "keyword_activity" with "123"
    And I click the search grant number button 
    Then I should see these entries
      | activity_number |
      | 111123          |            
      | 123123          | 
      | 123345          | 
      
  #CRATEIT-161
  @wip
  Scenario: Add and Remove Grant Numbers
    Given I fill in 'keyword_activity' with '123'
    And I click the search grant number button 
    And I add grant number "123345" to the list
    And I add grant number "111123" to the list
    Then I should see these entries in the selected grant number list
      | activity_number | 
      | 123345          | 
      | 111123          | 
    And I remove grant number "123345" in the list
    Then I should see these entries in the selected grant number list
      | activity_number | 
      | 111123          | 
    And I add grant number "123123" to the list
    Then I should see these entries in the selected grant number list
      | activity_number | 
      | 111123          | 
      | 123123          | 
      
   #CRATEIT-162
   @wip
   Scenario: Grant number lookup result should exclude selected numbers
     Given I fill in 'keyword_activity' with '123'
     And I click the search grant number button 
     And I add grant number '123123' to the list
     And I click the 'Search Grant Number' button
     Then I should see these entries in the selected grant number list
       | activity_number |
       | 111123          | 
       | 123345          | 
   
   #CRATEIT-162
   @wip
   Scenario: Mint server unavailable should trigger a notification
     Given the mint server became unavailable
     When I fill in 'keyword_activity' with '123'
     And I click the 'Search Grant Number' button
     Then I should see 'Server unreachable. Please try again later.'
     
   #CRATEIT-162
   @wip
   Scenario: Server returns no results should trigger a notification
     Given I fill in 'keyword_activity' with 'abc'   
     And I click the search grant number button 
     Then I should see 'Sorry, your search returned no results. Please check and try again later'
     
     
   #CRATEIT-162
   @wip
   Scenario: Click on 'Clear All' should remove all selected grant numbers
     Given I fill in 'keyword_activity' with '123'
     And I click the 'Search Grant Number' button
     And I add grant number "123345" to the list
     And I add grant number "111123" to the list
     Then I click the 'Clear All' button
     Then I should see no entries in the selected grant number list
    
    
    
    
      