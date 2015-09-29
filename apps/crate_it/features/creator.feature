@javascript
Feature: Search, add and remove creators 
  As a user
  I want to add crators related to the crate
  
  Background:
    Given the browser is maximised
    And I have no crates
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
    Then I should see these entries in the selected creators list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr Daniel Johns | dan@silverchair.com |
    And I remove creator "john@smith.com" from the selected list
    Then I should see these entries in the selected creators list
      | name            | email               |
      | Mr Daniel Johns | dan@silverchair.com |
    And I add creator "2" to the list
    Then I should see these entries in the selected creators list
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
     Then I should see these entries in the selected creators list
      | name            | email               |
      | Prof John Smith | john@smith.com      |
      | Mr Daniel Johns | dan@silverchair.com |
      
    #CRATEIT-177
    Scenario: A user can manually add a creator
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      When I press "Add" on the popup dialog
      Then I should see these entries in the selected creators list
        | name       | email          |
        | Joe Bloggs | joe@bloggs.org |

    #CRATEIT-177
    Scenario: A user can cancel manually adding a creator
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      When I press "Add" on the popup dialog
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Elvis               |
        | add-creator-email | elvis@graceland.org |
      When I press "Cancel" on the popup dialog
      Then I should see these entries in the selected creators list
        | name       | email          |
        | Joe Bloggs | joe@bloggs.org |
    
    #CRATEIT-177, CRATEIT-196
    Scenario: A user can remove a manually added creator
      When I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      Then I press "Add" on the popup dialog
      And I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Elvis               |
        | add-creator-email | elvis@graceland.org |
      Then I press "Add" on the popup dialog
      And I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Elvis               |
        | add-creator-email | elvis@graceland.org |
      Then I press "Add" on the popup dialog
      Then I should see these entries in the selected creators list
        | name       | email               |
        | Joe Bloggs | joe@bloggs.org      |   
        | Elvis      | elvis@graceland.org |        
        | Elvis      | elvis@graceland.org |
      When I remove creator "elvis@graceland.org" from the selected list
      Then I should see these entries in the selected creators list
        | name       | email               |
        | Joe Bloggs | joe@bloggs.org      |          
        | Elvis      | elvis@graceland.org |
      And I remove creator "elvis@graceland.org" from the selected list
      Then I should see these entries in the selected creators list
        | name       | email               |
        | Joe Bloggs | joe@bloggs.org      |   

      #CRATEIT-177
      Scenario: A manually added creator name is mandatory
        When I click on "add-creator"
        And I fill in "add-creator-name" with "  "
        Then I should see "Name is required"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-177
      Scenario: A manually added creator name has a maximum length of 256 characters
        When I click on "add-creator"
        And I fill in "add-creator-name" with a long string of 257 characters
        Then I should see "Name must not be more than 256 characters"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-197
      Scenario: A manually added creator email is mandatory
        When I click on "add-creator"
        And I fill in "add-creator-name" with "Elvis"
        And I fill in "add-creator-email" with "  "
        Then I should see "Email is required"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-177
      Scenario: A manually added creator email has a maximum length of 128 characters
        When I click on "add-creator"
        And I fill in "add-creator-name" with "Elvis"
        And I fill in "add-creator-email" with a long string of 129 characters
        Then I should see "Email must not be more than 128 characters"
        And the "Add" button in the popup dialog should be disabled

      #CRATEIT-177
      Scenario: A manually added creator email must be a valid email address
        When I click on "add-creator"
        And I fill in "add-creator-name" with "Elvis"
        And I fill in "add-creator-email" with "elvis"
        Then I should see "Must be a valid email address"
        And the "Add" button in the popup dialog should be disabled
        And I fill in "add-creator-email" with "elvis@graceland.org"
        Then I press "Add" on the popup dialog
        Then I should see these entries in the selected creators list
          | name       | email               |
          | Elvis      | elvis@graceland.org |

      #CRATEIT-183, CRATEIT-198
      Scenario: A user can edit a manually added creator
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        # duplicate an entry
        And I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        # create a new entry
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in the following:
          | edit-creators-name  | Elvis               |
          | edit-creators-email | elvis@graceland.org |
        Then I press "Save" on the popup dialog
        Then I should see these entries in the selected creators list
          | name       | email               |
          | Joe Bloggs | joe@bloggs.org      |
          | Elvis      | elvis@graceland.org |

      #CRATEIT-183
      Scenario: A user can cancel editing a manually added creator
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in the following:
          | edit-creators-name  | Elvis               |
          | edit-creators-email | elvis@graceland.org |
        Then I press "Cancel" on the popup dialog
        Then I should see these entries in the selected creators list
          | name       | email               |
          | Joe Bloggs | joe@bloggs.org      |

      #CRATEIT-183
      Scenario: A manually edited creator from the mint displays original details
        Given I fill in "keyword_creator" with "John"
        And I click the search creator button
        And I add creator "john@smith.com" to the selected list
        When I edit creator "john@smith.com"
        And I fill in "edit-creators-name" with "Elvis"
        And I fill in "edit-creators-email" with ""
        And I fill in "edit-creators-email" with "elvis@graceland.org"
        Then I click the save editor button
        When I edit creator "elvis@graceland.org"
        Then I should see the following:
          | original-creators-name | Prof John Smith     |
          | original-creators-email| john@smith.com      |
          | edit-creators-name     | Elvis               |
          | edit-creators-email    | elvis@graceland.org |

      #CRATEIT-183
      Scenario: A manually edited creator name is mandatory
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in "edit-creators-name" with "  "
        Then I should see "Name is required"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-183
      Scenario: A manually edited creator name has a maximum length of 256 characters
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in "edit-creators-name" with a long string of 257 characters
        Then I should see "Name must not be more than 256 characters"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-183
      Scenario: A manually edited creator email is required
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in "edit-creators-name" with "Elvis"
        And I fill in "edit-creators-email" with ""
        And I wait for 2 seconds
        Then I should see "Email is required"

      #CRATEIT-183
      Scenario: A manually edited creator email has a maximum length of 128 characters
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in "edit-creators-name" with "Elvis"
        And I fill in "edit-creators-email" with a long string of 129 characters
        Then I should see "Email must not be more than 128 characters"
        And the "Save" button in the popup dialog should be disabled

      #CRATEIT-183
      Scenario: A manually edited creator email must be a valid email address
        When I click on "add-creator"
        And I fill in the following:
          | add-creator-name  | Joe Bloggs     |
          | add-creator-email | joe@bloggs.org |
        Then I press "Add" on the popup dialog
        When I edit creator "joe@bloggs.org"
        And I fill in "edit-creators-name" with "Elvis"
        And I fill in "edit-creators-email" with ""
        And I fill in "edit-creators-email" with "elvis"
        Then I should see "Must be a valid email address"
        And the "Save" button in the popup dialog should be disabled
        And I fill in "edit-creators-email" with "elvis@graceland.org"
        Then I press "Save" on the popup dialog
        Then I should see these entries in the selected creators list
          | name       | email               |
          | Elvis      | elvis@graceland.org |

    #CRATEIT-199
    Scenario: A manually added a creators's fields are still mandatory after a previous add
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      When I press "Add" on the popup dialog
      When I click on "add-creator"
      Then the "Add" button in the popup dialog should be disabled

    #CRATEIT-199
    Scenario: A manually added a creators's fields are still mandatory after a previous cancel
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      When I press "Cancel" on the popup dialog
      When I click on "add-creator"
      Then the "Add" button in the popup dialog should be disabled

    #CRATEIT-212
    Scenario: A manually added creator identifier is optional
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      And I fill in "add-creator-identifier" with "   "
      Then I should not see "Identifier is required"
      And the "Add" button in the popup dialog should not be disabled

    #CRATEIT-212
    Scenario: A manually added creator identifier must be a URL
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      And I fill in "add-creator-identifier" with "test.org/test"
      Then I should see "Must be a valid URL"
      And the "Add" button in the popup dialog should be disabled
      When I fill in "add-creator-identifier" with "http://test.org/test"
      Then I should not see "Must be a valid URL"
      Then the "Add" button in the popup dialog should not be disabled

    #CRATEIT-212
    Scenario: A manually added creator identifier must be less than 2001 characters
      When I click on "add-creator"
      Then I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      And I fill in "add-creator-identifier" with a long string of 2001 characters
      Then I should see "Identifier must not be more than 2000 characters"
      And the "Add" button in the popup dialog should be disabled

    #CRATEIT-212
    Scenario: A manually edited creator identifier is optional
      When I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      Then I press "Add" on the popup dialog
      When I edit creator "joe@bloggs.org"
      And I fill in "edit-creators-identifier" with "   "
      Then I should not see "Identifier is required"
      And the "Save" button in the popup dialog should not be disabled

    #CRATEIT-212
    Scenario: A manually edited creator identifier must be a URL
      When I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      Then I press "Add" on the popup dialog
      When I edit creator "joe@bloggs.org"
      And I fill in "edit-creators-identifier" with "test.org/test"
      Then I should see "Must be a valid URL"
      And the "Save" button in the popup dialog should be disabled
      When I fill in "edit-creators-identifier" with "http://test.org/test"
      Then I should not see "Must be a valid URL"
      Then the "Save" button in the popup dialog should not be disabled

    #CRATEIT-212
    Scenario: A manually edited creator identifier must be less than 2001 characters
      When I click on "add-creator"
      And I fill in the following:
        | add-creator-name  | Joe Bloggs     |
        | add-creator-email | joe@bloggs.org |
      Then I press "Add" on the popup dialog
      When I edit creator "joe@bloggs.org"
      And I fill in "edit-creators-identifier" with a long string of 2001 characters
      Then I should see "Identifier must not be more than 2000 characters"
      And the "Save" button in the popup dialog should be disabled

    #CRATEIT-212
    Scenario: A creator edited from the mint does not display URL
      Given I fill in "keyword_creator" with "John"
      And I click the search creator button
      And I add creator "1" to the list
      When I edit creator "john@smith.com"
      Then I should not see "Creator Identifier URL"

    #CRATEIT-224
    Scenario: A creator edited from the mint does not display URL
      Given I fill in "keyword_creator" with "John"
      And I click the search creator button
      And I add creator "1" to the list
      When I edit creator "john@smith.com"
      And I fill in "edit-creators-email" with "john@smith.org"
      Then the "Save" button in the popup dialog should not be disabled

