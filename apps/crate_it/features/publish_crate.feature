@javascript
Feature: Publish crates to an endpoint
  As a user
  I want to publish crates to an endpoint
  
  Background:
    Given I have no crates
    And I'm logged in to ownCloud as "test"
    And I go to the crate_it page


  #CRATEIT-178
  Scenario: A user can see a metadata summary before publishing
    When I click on "add-creator"
    Then I fill in the following:
      | add-creator-name  | Joe Bloggs     |
      | add-creator-email | joe@bloggs.org |
    When I press "Add" on the popup dialog
    And I expand the grant number metadata section
    When I click on "add-activity"
    Then I fill in the following:
      | add-grant-number      | 123123              |
      | add-grant-year        | 2007                |
      | add-grant-institution | The Ponds Institute |
      | add-grant-title       | Anti Aging Creams   |
    When I press "Add" on the popup dialog
    When I click on "publish"
    Then I should see "Joe Bloggs"
    And I should see "joe@bloggs.org"
    And I should see "123123"
    And I should see "2007"
    And I should see "Anti Aging Creams"

    #CRATEIT-59
    #CRATEIT-212
    Scenario: A user sees a message confirming a successful publish
      Given that I can publish a crate
      When I click on "publish"
      And I press "Publish" on the popup dialog
      Then I should see "default_crate successfully published to test collection"
      And I wait for 10 seconds

    #CRATEIT-59
    Scenario: A user can cancel publishing a crate
      Given that I can publish a crate
      When I click on "publish"
      And I press "Cancel" on the popup dialog
      Then I should not see "default_crate successfully published to test collection"

    #CRATEIT-59
    #CRATEIT-212
    Scenario: A user sees an error message if there were problems publishing a crate
      Given that I can not publish a crate
      When I click on "publish"
      And I press "Publish" on the popup dialog
      Then I should see "Error: there were problems zipping the crate"

    #CRATEIT-59
    Scenario: Publishing a crate checks for consistency
      Given I have no files
      And I have file "file.txt" within the root folder
      And I go to the files page
      When I add "file.txt" to the current crate
      And I have no files
      And I go to the crate_it page
      When I click on "publish"
      Then I should see "The following item no longer exists"

    #CRATEIT-212
    Scenario: Publish confirm allows user to enter email address for confirmation
      Given that I can not publish a crate
      When I click on "publish"
      And I press "Publish" on the popup dialog
      Then I should see "Enter an email address to send the publish log to"
      Then the "Send" button in the popup dialog should be disabled
      When I fill in "publish-confirm-email" with "test@test.org"
      Then the "Send" button in the popup dialog should not be disabled

    #CRATEIT-212
    Scenario: Publish confirm email address must be valid
      Given that I can not publish a crate
      When I click on "publish"
      And I press "Publish" on the popup dialog
      When I fill in "publish-confirm-email" with "sdfd"
      Then I should see "Must be a valid email address"
      Then the "Send" button in the popup dialog should be disabled



