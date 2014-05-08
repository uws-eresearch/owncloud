<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\MinkExtension\Context\MinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';

// require_once '../../vendor/autoload.php';
// require_once 'vendor/autoload.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
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
     * @Given /^I\'m logged in to ownCloud as "([^"]*)"$/
     */
    public function iMLoggedInToOwncloudAs2($user)
    {
        $this->visit('/owncloud');
        $this->fillField('user', $user);
        $this->fillField('password', $user);
        $this->pressButton('submit');
        sleep(10);
    }

    /**
     * @When /^I go to the crate_it page$/
     */
    public function iGoToTheCrateItPage()
    {
        $this->visit('/owncloud/index.php/apps/crate_it');
    }

    /**
     * @Then /^I should see the default crate already created and selected$/
     */
    public function iShouldSeeTheDefaultCrateAlreadyCreatedAndSelected()
    {
        throw new PendingException();
    }

       /**
     * @Given /^I have file "([^"]*)" within the root folder$/
     */
    public function iHaveFileWithinTheRootFolder($file)
    {
        $this->iHaveFileWithin($file, "");
    }

    /**
     * @Given /^I have folder "([^"]*)" within the root folder$/
     */
    public function iHaveFolderWithinTheRootFolder($folder)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I have folder "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFolderWithin($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I have file "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFileWithin($file, $folder)
    {
        $this->visit('/owncloud/index.php/apps/files?dir='.$folder);
        $page = $this->getSession()->getPage();
        $page->find('css', 'li[data-type=file]')->click();
    }

    /**
     * @When /^I add a "([^"]*)" within the root folder to the default crate$/
     */
    public function iAddAWithinTheRootFolderToTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^the default crate should contain "([^"]*)" in the root folder$/
     */
    public function theDefaultCrateShouldContainInTheRootFolder($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I add "([^"]*)" within the root folder to the default crate$/
     */
    public function iAddWithinTheRootFolderToTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^the default crate should contain "([^"]*)" within the root folder$/
     */
    public function theDefaultCrateShouldContainWithinTheRootFolder($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the default crate should contain "([^"]*)" within "([^"]*)"$/
     */
    public function theDefaultCrateShouldContainWithin($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then /^"([^"]*)" should not be visible in the default crate$/
     */
    public function shouldNotBeVisibleInTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I expand the root folder in the default crate$/
     */
    public function iExpandTheRootFolderInTheDefaultCrate()
    {
        throw new PendingException();
    }

    /**
     * @Then /^"([^"]*)" should be visible in the default crate$/
     */
    public function shouldBeVisibleInTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I add a "([^"]*)" to the default crate$/
     */
    public function iAddAToTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^the default crate should contain "([^"]*)" within the root folder, in that order$/
     */
    public function theDefaultCrateShouldContainWithinTheRootFolderInThatOrder($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I add "([^"]*)" to the default crate$/
     */
    public function iAddToTheDefaultCrate($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I add folder "([^"]*)" within "([^"]*)" to the default crate$/
     */
    public function iAddFolderWithinToTheDefaultCrate($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given /^the default crate should not contain "([^"]*)" within the root folder$/
     */
    public function theDefaultCrateShouldNotContainWithinTheRootFolder($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When /^I delete the default crate$/
     */
    public function iDeleteTheDefaultCrate()
    {
        throw new PendingException();
    }

}
