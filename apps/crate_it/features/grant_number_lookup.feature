@javascript
Feature: Search, add and remove grant number 
  As a user
  I want to add grant number related to the crate
  
  Background:
    Given the browser is maximised
    And I have no crates
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
     And I wait for 30 seconds
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
      
    #CRATEIT-184
    Scenario: A user can manually add a grant
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      When I press "Add" on the popup dialog
      Then I should see these entries in the selected grant number list
      | grant    | year | title             |
      | 123123   | 2007 | Anti Aging Creams |
    
    #CRATEIT-184
    Scenario: A user can cancel manually add a grant
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      When I press "Add" on the popup dialog
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123456             |
        | add-grant-year        | 2009               |
        | add-grant-institution | College University |
        | add-grant-title       | Monkey Physics     |
      When I press "Cancel" on the popup dialog
      Then I should see these entries in the selected grant number list
      | grant    | year | title             |
      | 123123   | 2007 | Anti Aging Creams |

    #CRATEIT-184
    Scenario: A user can remove a manually added grant
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      When I press "Add" on the popup dialog
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123456             |
        | add-grant-year        | 2009               |
        | add-grant-institution | College University |
        | add-grant-title       | Monkey Physics     |
      When I press "Add" on the popup dialog
      # duplicate an entry
      And I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123456             |
        | add-grant-year        | 2009               |
        | add-grant-institution | College University |
        | add-grant-title       | Monkey Physics     |
      And I press "Add" on the popup dialog
      Then I should see these entries in the selected grant number list
      | grant    | year | title             |
      | 123123   | 2007 | Anti Aging Creams |
      | 123456   | 2009 | Monkey Physics    |
      | 123456   | 2009 | Monkey Physics    |
      When I remove grant "123456" from the selected list
      Then I should see these entries in the selected grant number list
      | grant    | year | title             |
      | 123123   | 2007 | Anti Aging Creams |
      | 123456   | 2009 | Monkey Physics    |
      And I remove grant "123456" from the selected list
      Then I should see these entries in the selected grant number list
      | grant    | year | title             |
      | 123123   | 2007 | Anti Aging Creams |

      #CRATEIT-184
      Scenario: A manually added grant number is mandatory
        When I click on "add-activity"
        And I fill in "add-grant-number" with "  "
        Then I should see "Grant number is required"
        And the "Add" button in the popup dialog should be disabled
      
      #CRATEIT-184
      Scenario: A manually added grant number can not be longer than 256 characters
        When I click on "add-activity"
        And I fill in "add-grant-number" with a long string of 257 characters
        Then I should see "Grant number must not be more than 256 characters"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-184
      Scenario: A manually added grant year is mandatory
        When I click on "add-activity"
        And I fill in "add-grant-year" with "  "
        Then I should see "Year is required"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-184
      Scenario: A manually added grant year must be a 4 digit year
        When I click on "add-activity"
        And I fill in "add-grant-year" with "Meow"
        Then I should see "Must be a valid year"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-184
      Scenario: A manually added grant institution is mandatory
        When I click on "add-activity"
        And I fill in "add-grant-institution" with "  "
        Then I should see "Institution is required"
        And the "Add" button in the popup dialog should be disabled
      
      #CRATEIT-184
      Scenario: A manually added grant institution can not be longer than 256 characters
        When I click on "add-activity"
        And I fill in "add-grant-institution" with a long string of 257 characters
        Then I should see "Institution must not be more than 256 characters"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-184
      Scenario: A manually added grant title is mandatory
        When I click on "add-activity"
        And I fill in "add-grant-title" with "  "
        Then I should see "Title is required"
        And the "Add" button in the popup dialog should be disabled
      
      #CRATEIT-184
      Scenario: A manually added grant title can not be longer than 256 characters
        When I click on "add-activity"
        And I fill in "add-grant-title" with a long string of 257 characters
        Then I should see "Title must not be more than 256 characters"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: A user can edit a manually added a grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        # Duplicate entry
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        Then I fill in the following:
          | edit-activities-grant_number| 123456             |
          | edit-activities-date        | 2009               |
          | edit-activities-institution | College University |
          | edit-activities-title       | Monkey Physics     |
        When I press "Save" on the popup dialog
        Then I should see these entries in the selected grant number list
          | grant    | year | title             |
          | 123123   | 2007 | Anti Aging Creams |
          | 123456   | 2009 | Monkey Physics    |

      #CRATEIT-185
      Scenario: A user can cancel editing a manually added a grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        Then I fill in the following:
          | edit-activities-grant_number| 123456             |
          | edit-activities-date        | 2009               |
          | edit-activities-institution | College University |
          | edit-activities-title       | Monkey Physics     |
        When I press "Cancel" on the popup dialog
        Then I should see these entries in the selected grant number list
          | grant    | year | title             |
          | 123123   | 2007 | Anti Aging Creams |
        
      #CRATEIT-185
      Scenario: A user can not edit a grant added from the mint
        Given I fill in "keyword_activity" with "123"
        And I click the search grant number button 
        And I add creator "123123" to the selected list
        When I edit grant "123123"
        Then I should not see "Add Grant"

      #CRATEIT-185
      Scenario: Grant number is mandatory in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-grant_number" with "  "
        Then I should see "Grant number is required"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Grant number can not be longer than 256 characters in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-grant_number" with a long string of 257 characters
        Then I should see "Grant number must not be more than 256 characters"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Year is mandatory in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-date" with "  "
        Then I should see "Year is required"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Year must be a 4 digit year in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-date" with "Meow"
        Then I should see "Must be a valid year"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Institution is mandatory in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-institution" with "  "
        Then I should see "Institution is required"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Institution can not be longer than 256 characters in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-institution" with a long string of 257 characters
        Then I should see "Institution must not be more than 256 characters"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Title is mandatory in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-title" with "  "
        Then I should see "Title is required"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-185
      Scenario: Title can not be longer than 256 characters in a manually edited grant
        When I click on "add-activity"
        Then I fill in the following:
          | add-grant-number      | 123123              |
          | add-grant-year        | 2007                |
          | add-grant-institution | The Ponds Institute |
          | add-grant-title       | Anti Aging Creams   |
        When I press "Add" on the popup dialog
        When I edit grant "123123"
        And I fill in "edit-activities-title" with a long string of 257 characters
        Then I should see "Title must not be more than 256 characters"
        And the "Save" button in the popup dialog should be disabled

    #CRATEIT-199
    Scenario: A manually added a grant's fields are still mandatory after a previous add
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      When I press "Add" on the popup dialog
      When I click on "add-activity"
      Then the "Add" button in the popup dialog should be disabled

    #CRATEIT-199
    Scenario: A manually added a grant's fields are still mandatory after a previous cancel
      When I click on "add-activity"
      Then I fill in the following:
        | add-grant-number      | 123123              |
        | add-grant-year        | 2007                |
        | add-grant-institution | The Ponds Institute |
        | add-grant-title       | Anti Aging Creams   |
      When I press "Cancel" on the popup dialog
      When I click on "add-activity"
      Then the "Add" button in the popup dialog should be disabled