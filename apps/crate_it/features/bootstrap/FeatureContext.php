<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given /^I\'m logged in to ownCloud$/
     */  
    public function iMLoggedInToOwncloud()
    {
        throw new PendingException();
    }

    /**
     * @When /^I go to the crate_it page$/
     */
    public function iGoToTheCrateItPage()
    {
        throw new PendingException();
    }

    /**
     * @Then /^I should see the default crate already created and selected$/
     */
    public function iShouldSeeTheDefaultCrateAlreadyCreatedAndSelected()
    {
        throw new PendingException();
    }
}
