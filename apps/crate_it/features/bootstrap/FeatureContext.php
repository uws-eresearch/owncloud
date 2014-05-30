<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\WebAssert;

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

        sleep(3); // allow the page to load
    }

    /**
     * @When /^I go to the crate_it page$/
     */
    public function iGoToTheCrateItPage()
    {
        $this->visit('/owncloud/index.php/apps/crate_it');
    }

    /**
     * @When /^I navigate to folder(\d+)$/
     */
    public function iNavigateToFolder($arg1)
    {
        $this->visit('/owncloud/index.php/apps/files?dir=/folder1');
    }

    /**
     * @Given /^I go to the files page$/
     */
    public function iGoToTheFilesPage()
	{
		$this->visit('/owncloud/index.php/apps/files');
	}

    /**
     * @Then /^I should see the default crate already created and selected$/
     */
    public function iShouldSeeTheDefaultCrateAlreadyCreatedAndSelected()
    {
        $page = $this->getSession()->getPage();
    	$optionElement = $page->find('xpath', '//select[@id="crates"]/option[@selected]');
		if (!$optionElement) 
		{
			throw new Exception('No default value specified in the crate select dropdown');
		}
		$selectedDefaultValue = (string)$optionElement->getText();
		if ($selectedDefaultValue != "default_crate")
		{
			throw new Exception('Default value is "' . $selectedDefaultValue . '" , not "default_crate"');
		}
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
    public function iHaveFolderWithinTheRootFolder($new_folder)
    {
        $this->iHaveFolderWithin($new_folder, "");
    }

    /**
     * @Given /^I have folder "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFolderWithin($new_folder, $folder)
    {
        $this->visit('/owncloud/index.php/apps/files?dir='.$folder);
        $page = $this->getSession()->getPage();
        $page->find('css', '#new > a')->click();
        $page->find('xpath', '//div[@id="new"]//li[@data-type="folder"]/p')->click();
        try { // NOTE: The element disappears after setting the value causing an exception
            $page->find('xpath', '//div[@id="new"]//li[@data-type="folder"]//input')->setValue($new_folder."\n");
        } catch(Exception $e) {
            // Do nothing
        }
        $this->visit('/owncloud/index.php/apps/files?dir=');
    }

    /**
     * @Given /^I have file "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFileWithin($file, $folder)
    {
        $this->visit('/owncloud/index.php/apps/files?dir='.$folder);
        $page = $this->getSession()->getPage();
        $page->find('css', '#new > a')->click();
        $page->find('xpath', '//div[@id="new"]//li[@data-type="file"]/p')->click();
        try { // NOTE: The element disappears after setting the value causing an exception
        	$filename = strstr($file,'.',true); // ditch file suffix 
            $page->find('xpath', '//div[@id="new"]//input')->setValue($filename."\n");
        } catch(Exception $e) {
            // Do nothing
        }
        $this->visit('/owncloud/index.php/apps/files?dir=');
    }

    /**
     * @Then /^the default crate should contain "([^"]*)" within the root folder$/
     */
    public function theDefaultCrateShouldContainWithinTheRootFolder($arg1)
    {
        $page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $web_assert->elementExists('xpath', '//div[@id="files"]/ul/li', $page);
		////div[@id="files"]/ul/li/ul/li//span[text()="file.txt"]
 	    $web_assert->elementExists('xpath', '/ul/li//span[text()="'. $arg1 .'"]', $root_folder);   	
	}
	
	/**
     * @Then /^the default crate should not contain "([^"]*)" anywhere$/
     */
    public function theDefaultCrateShouldNotContainAnywhere($arg1)
    {
        $page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $web_assert->elementExists('xpath', '//div[@id="files"]/ul/li', $page);
		////div[@id="files"]/ul/li/ul/li//span[text()="file.txt"]
 	    $web_assert->elementNotExists('xpath', '//ul/li//span[text()="'. $arg1 .'"]', $root_folder);   	
	}
	
    /**
     * @When /^I add "([^"]*)" to the default crate$/
     */
    public function iAddToTheDefaultCrate($arg1)
    {
        $page = $this->getSession()->getPage();
        $page->find('xpath', '//tr[@data-file="' . $arg1. '"]//label')->click();
		$page->find('xpath', '//tr[@data-file="' . $arg1. '"]//a[@data-action="Add to crate"]')->click();
    }

    /**
     * @Given /^the default crate should contain "([^"]*)" within "([^"]*)"$/
     */
    public function theDefaultCrateShouldContainWithin($arg1, $arg2)
    {
        $page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $web_assert->elementExists('xpath', '//div[@id="files"]/ul/li', $page);
		$parent_folder = $web_assert->elementExists('xpath', '//ul/li/div/span[text()="'.$arg2. '"]', $root_folder);
		$web_assert->elementExists('xpath', '/../../ul/li/div/span[text()="'.$arg1. '"]', $parent_folder);
    }

    /**
     * @Then /^"([^"]*)" should not be visible in the default crate$/
     */
    public function shouldNotBeVisibleInTheDefaultCrate($crateItem)
    {
		$page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $page->find('xpath', '//div[@id="files"]/ul/li');
		// The element will still exist even without being visible!
        $element = $web_assert->elementExists('xpath','//ul/li/div/span[text()="'.$crateItem. '"]', $root_folder);	
    	if ($element->isVisible())
		{
			throw new Exception('The element should be invisible.');
		}
	}

    /**
     * @When /^I expand the root folder in the default crate$/
     */
    public function iExpandTheRootFolderInTheDefaultCrate()
    {
        $page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $page->find('xpath', '//div[@id="files"]/ul/li');
		$arrow_link = $web_assert->elementExists('xpath','/div/a[contains(@class,"jqtree-toggler")]', $root_folder);
    	$arrow_link->click();
	}

    /**
     * @Then /^"([^"]*)" should be visible in the default crate$/
     */
    public function shouldBeVisibleInTheDefaultCrate($arg1)
    {
        $page = $this->getSession()->getPage();
		$web_assert = new WebAssert($this->getSession());
        $root_folder = $page->find('xpath', '//div[@id="files"]/ul/li');
        $element = $web_assert->elementExists('xpath','//ul/li/div/span[text()="'.$arg1. '"]', $root_folder);	
    	if (!$element->isVisible())
		{
			throw new Exception('The element should be visible');
		}
	}


    /**
     * @When /^I delete the default crate$/
     */
    public function iDeleteTheDefaultCrate()
    {
       $page = $this->getSession()->getPage();
       $page->find('css', 'a[id=delete]')->click();
       $this->confirmPopup();
	}
	
	/**
     * @Then /^the default crate should contain "([^"]*)" within the root folder, in that order$/
     */
    public function theDefaultCrateShouldContainWithinTheRootFolderInThatOrder($arg1)
    {
        $page = $this->getSession()->getPage();
    	$web_assert = new WebAssert($this->getSession());
		$node_order = $page->findAll('xpath', '//div[@id="files"]/ul/li//ul/li/div/span/text()');
		$expected_order = str_split($arg1);
		$difference = array_diff($node_order, $expected_order);
		if ( count($difference) > 0)
		{
			throw new Exception('The node order is "' .$node_order. '" instead of "' .$expected_order. '"');
		}
    }
	
    /**
     * @when /^(?:|I )confirm the popup$/
     */
    public function confirmPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }
 
    /**
     * @when /^(?:|I )cancel the popup$/
     */
    public function cancelPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    /**
     * @Given /^I have no files$/
     */
    public function iHaveNoFiles()
    {
    	
        $page = $this->getSession()->getPage();
        $page->find('css', 'label[for=select_all]')->click();
		$page->find('css', 'a[class=delete-selected]')->click();
    }

    /**
     * @Given /^I wait for (\d+) seconds$/
     */
    public function iWaitForSeconds($seconds)
    {
        sleep($seconds);
    }

    /**
     * @When /^I remove "([^"]*)"$/
     */
    public function iRemove($crateItem)
    {
        $this->performActionElByFAIcon($crateItem, 'fa-trash-o');
    }

    /**
     * @When /^I rename "([^"]*)"$/
     */
    public function iRename($crateItem)
    {
        $this->performActionElByFAIcon($crateItem, 'fa-pencil');
    }

    /**
     * @Then /^"([^"]*)" should be in the crate$/
     */
    public function shouldBeInTheCrate($crateItem)
    {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]';
        $page = $this->getSession()->getPage();
        $item = $page->find('xpath', $xpath);
        if (is_null($item)) {
            throw new Exception($crateItem." is in the crate");
        }
    }

    /**
     * @Then /^"([^"]*)" should not be in the crate$/
     */
    public function shouldNotBeInTheCrate($crateItem)
    {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]';
        $page = $this->getSession()->getPage();
        $item = $page->find('xpath', $xpath);
        if (!is_null($item)) {
            throw new Exception($crateItem." is in the crate");
        }
    }

    /**
     * @Then /^I press "([^"]*)" on the popup dialog$/
     */
    public function iPressOnThePopupDialog($buttonText)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', '.modal.in');
        $el->find('xpath', '//button[text() = "'.$buttonText.'"]')->click();
    }

    /**
     * @When /^I toggle expand on "([^"]*)"$/
     */
    public function iToggleExpandOn($folder)
    {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$folder.'"]/preceding-sibling::a';
        $page = $this->getSession()->getPage();
        $el = $page->find('xpath', $xpath);
        $el->click();
    }

    /**
     * @Then /^I should have crate actions "([^"]*)" for "([^"]*)"$/
     */
    public function iShouldHaveCrateActionsFor($actions, $crateItem)
    {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]/following-sibling::ul';
        $page = $this->getSession()->getPage();
        $el = $page->find('xpath', $xpath);
        $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
        $actionItems = explode(', ', $actions);
        $web_assert = new WebAssert($this->getSession());
        foreach ($actionItems as $action) {
            $web_assert->elementExists('xpath', $xpath.'//a[text() = "'.$action.'"]');
        }
        
    }

    /**
     * @Given /^I should not have crate actions "([^"]*)" for "([^"]*)"$/
     */
    public function iShouldNotHaveCrateActionsFor($actions, $crateItem)
    {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]/following-sibling::ul';
        $page = $this->getSession()->getPage();
        $el = $page->find('xpath', $xpath);
        $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
        $actionItems = explode(', ', $actions);
        $web_assert = new WebAssert($this->getSession());
        foreach ($actionItems as $action) {
            $web_assert->elementNotExists('xpath', $xpath.'//a[text() = "'.$action.'"]');
        }
    }

    /**
     * Gets a file action element by crate item name font-awesome icon class
     **/
    private function performActionElByFAIcon($crateItem, $fa_icon_class) {
        $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]';
        $page = $this->getSession()->getPage();
        $el = $page->find('xpath', $xpath);
        $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
        $xpath = '/following-sibling::ul//i[@class="fa '.$fa_icon_class.'"]';
        $el = $el->find('xpath', $xpath);
        $el->click();
    }
	
	/**
     * @Given /^I delete all existing crates$/
     */
    public function iDeleteAllExistingCrates()
    {
        //Get an instance of BagItManager
		$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();
		$page = $this->getSession()->getPage();
		$xpath = '//select[@id="crates"]//option/@id';
		$existing_crate_name_els = $page->findAll('xpath', $xpath);
		foreach ($existing_crate_name_els as $crate_name_el) {
			$bagit_manager->switchCrate($crate_name_el.getText());
			$result = $bagit_manager->deleteCrate();
		}
		
    }
	
    /**
     * @When /^I click the new crate button$/
     */
    public function iClickTheNewCrateButton()
    {
        $page = $this->getSession()->getPage();
		$xpath = '//a[@id="subbutton"]';
        $page->find('xpath', $xpath)->click();
    }

    /**
     * @Then /^I click "([^"]*)" in the create crate modal$/
     */
    public function iClickInTheCreateCrateModal($buttonText)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', '.modal.in');
        $el->find('xpath', '//button[text() = "'.$buttonText.'"]')->click();
    }
    /**
     * @Then /^I should see notice "([^"]*)"$/
     */
    public function iShouldSeeNotice($arg1)
    {
        $page = $this->getSession()->getPage();
		$notification = $page->find('xpath', '//div[@id="notification"]');
    	$text = $notification->getText();
		if ($text!=$arg1) {
			throw new Exception('Notification should say "'.$arg1.'", but instead it says "'.$text.'"');
		}
    	if (!$notification->isVisible()) {
    		throw new Exception('Notification is not visible');
    	}
	}

    /**
     * @Given /^the selected crate should be "([^"]*)"$/
     */
    public function theSelectedCrateShouldBe($arg1)
    {
        $page = $this->getSession()->getPage();
    	$optionElement = $page->find('xpath', '//select[@id="crates"]/option[@selected]');
		$selectedDefaultValue = (string)$optionElement->getText();
		if ($selectedDefaultValue != $arg1)
		{
			throw new Exception('Selected value is "' . $selectedDefaultValue . '" , not "'. $arg1 .'".');
		}
    }	

    /**
     * @Given /^I should not have crate "([^"]*)"$/
     */
    public function iShouldNotHaveCrate($arg1)
    {
        $page = $this->getSession()->getPage();
		$xpath = '//select[@id="crates"]//option';
		$existing_crate_name_els = $page->findAll('xpath', $xpath);
		foreach ($existing_crate_name_els as $crate_name_el) {
			if ($crate_name_el->getAttribute("id") == $arg1)
			{
				throw new Exception('The crate "' .$arg1.'" should not exist');
			}
		}
    }
	
	/**
	 * @Then /^I should see error \'([^\']*)\' in the modal$/
	 *
     */
    public function iShouldSeeErrorInTheModal($arg1)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', '.modal.in');
		$error_label = $el->find('xpath', '//label[@name="Error Message"]');
		if (!$error_label->isVisible())
		{
			throw new Exception('Error message not visible');
		}
		$msg = $error_label->getText();
		if ($msg != $arg1)
		{
			throw new Exception('Error message is "' . $msg . '" , not "'. $arg1 .'".');
		}
    }
	
	/**
     * @Then /^I should see a "([^"]*)" validation error "([^"]*)"$/
     */
    public function iShouldSeeAValidationError($arg1, $arg2)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', '.modal.in');
		$validation_error_label = $el->find('xpath', '//label[@validates="'.$arg1.'"]');
		if (!$validation_error_label->isVisible())
		{
			throw new Exception('Validation message not visible');
		}
		$msg = $validation_error_label->getText();
		if ($msg != $arg2)
		{
			throw new Exception('Validation message is "' . $msg . '" , not "'. $arg2 .'".');
		}
    }
}

