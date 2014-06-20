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

    private static $file_root = '/var/www/html/owncloud/data/test/files/';

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
    public function iMLoggedInToOwncloudAs($user)
    {
        $this->visit('/owncloud');
        $this->fillField('user', $user);
        $this->fillField('password', $user);
        $this->pressButton('submit');
    }

    /**
     * @When /^I go to the crate_it page$/
     */
    public function iGoToTheCrateItPage()
    {
        $this->visit('/owncloud/index.php/apps/crate_it');
        $this->waitForPageToLoad();
    }

    /**
     * @When /^I navigate to folder(\d+)$/
     */
    public function iNavigateToFolder($arg1)
    {
        $this->visit('/owncloud/index.php/apps/files?dir=/folder1');
        waitForPageToLoad();
    }

    /**
     * @Given /^I go to the files page$/
     */
    public function iGoToTheFilesPage()
	{
		$this->visit('/owncloud/index.php/apps/files');
        waitForPageToLoad();
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
     * @Given /^I have folders? "([^"]*)"$/
     */
    public function iHaveFolders($folders)
    {
        $command = 'ssh -i ../../puphpet/files/dot/ssh/id_rsa -p 2222 root@127.0.0.1 \'mkdir -p '.self::$file_root.$folders.'\'';
        exec($command);
    }

    /**
     * @Given /^I have file "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFileWithin($file, $folder)
    {
        $folder = (!empty($folder) ? $folder.'/' : $folder);
        $command = 'ssh -i ../../puphpet/files/dot/ssh/id_rsa -p 2222 root@127.0.0.1 \'touch '.self::$file_root.$folder.$file.'\'';
        exec($command);
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
        $command = 'ssh -i ../../puphpet/files/dot/ssh/id_rsa -p 2222 root@127.0.0.1 \'rm -rf /var/www/html/owncloud/data/test/files/*\'';
        exec($command);
    }

    /**
     * @Given /^I wait for (\d+) seconds?$/
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
     * @When /^I add a virtual folder to "([^"]*)"$/
     */
    public function iAddAVirtualFolderTo($crateItem)
    {
        $this->performActionElByFAIcon($crateItem, 'fa-plus');
    }

    /**
     * @Given /^the crate should have name "([^"]*)"$/
     */
    public function theCrateShouldHaveName($name)
    {
        $xpath = '//div[@id="files"]//span[1]';
        $page = $this->getSession()->getPage();
        $item = $page->find('xpath', $xpath);
        if (is_null($item)) {
            throw new Exception($crateItem." is in the crate");
        }
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
     * @Given /^I have no crates$/
     */
    public function iHaveNoCrates()
    {
        $command = 'ssh -i ../../puphpet/files/dot/ssh/id_rsa -p 2222 root@127.0.0.1 \'rm -rf /var/www/html/owncloud/data/test/crates/\'';
        exec($command);
    }


    /**
     * @When /^I click the delete crate button$/
     */
    public function iClickTheDeleteCrateButton() {
        $page = $this->getSession()->getPage();
        $page->find('css', 'a[id=delete]')->click();
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
     * @Given /^I have crate "([^"]*)"$/
     */
    public function iHaveCrate($crateName)
    {
        $this->iClickTheNewCrateButton();
        $this->fillField('crate_input_name', $crateName);
        $this->iClickInTheCreateCrateModal('Create');
        sleep(2);
    }

    /**
     * @Given /^I should have crate "([^"]*)"$/
     */
    public function iShouldHaveCrate($crate)
    {
        $page = $this->getSession()->getPage();
        $web_assert = new WebAssert($this->getSession());
		$xpath = '//select[@id="crates"]//option[@id="'.$crate.'"]';
        $web_assert->elementExists('xpath', $xpath, $page);
		
    }
	
    /**
     * @Given /^I should not have crate "([^"]*)"$/
     */
    public function iShouldNotHaveCrate($crate)
    {
        $page = $this->getSession()->getPage();
        $web_assert = new WebAssert($this->getSession());
        $xpath = '//select[@id="crates"]//option[@id="'.$crate.'"]';
        $web_assert->elementNotExists('xpath', $xpath, $page);
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
	
	/**
     * @Given /^I should see the crate description "([^"]*)"$/
     */
    public function iShouldSeeTheCrateDescription($arg1)
    {
        $page = $this->getSession()->getPage();
		$xpath = '//div[@id="description_box"]/div[@id="description"]';
		$desc = $page->find('xpath', $xpath);
		if ($desc->getText() != $arg1)
		{
			throw new Exception('The crate should have description "' .$arg1);
		}	
    }

    /**
     * @When /^I clear the crate$/
     */
    public function iClearTheCrate()
    {
        $page = $this->getSession()->getPage();
        $page->find('css', '#clear')->click();
    }


    /**
     * @Then /^I fill in "([^"]*)" with a long string of (\d+) characters$/
     */
    public function iFillInWithALongStringOfCharacters($arg1, $arg2)
    {
        $value = str_repeat('a', $arg2 + 1);
		if (strlen($value) != $arg2+1)
		{
			throw new Exception('Repeat characters fail');
		}
		$page = $this->getSession()->getPage();
		$el = $page->find('css', '.modal.in');
		
		$web_assert = new WebAssert($this->getSession());
		$xpath = '//*[@id="'.$arg1.'"]';
		$desc = $web_assert->elementExists('xpath', $xpath);
		$desc->setValue($value);	
    }
	
    /**
     * @Given /^the selected crate name should be a long string truncated to (\d+) characters$/
     */
    public function theSelectedCrateNameShouldBeALongStringTruncatedToCharacters($arg1)
    {
        $page = $this->getSession()->getPage();
    	$optionElement = $page->find('xpath', '//select[@id="crates"]/option[@selected]');
		$selectedDefaultValue = (string)$optionElement->getText();
		if (strlen($selectedDefaultValue) != $arg1)
		{
			throw new Exception('Crate name is not "'. $arg1 .'" characters long.');
		}
    }
	
    /**
     * @Given /^the selected crate description should be a long string truncated to (\d+) characters$/
     */
    public function theSelectedCrateDescriptionShouldBeALongStringTruncatedToCharacters($arg1)
    {
        $page = $this->getSession()->getPage();
		$xpath = '//div[@id="description_box"]/div[@id="description"]';
		$desc = $page->find('xpath', $xpath);
		$desc_text = (string)$desc->getText();
		$desc_len = strlen($desc_text);
		if ($desc_len != $arg1)
		{
			throw new Exception('Crate description is not '. $arg1 .' characters long. It is '.$desc_len);
		}
    }
	
	/**
     * @Then /^the create crate modal should be clear of input and errors$/
     */
    public function theCreateCrateModalShouldBeClearOfInputAndErrors()
    {
        $page = $this->getSession()->getPage();
		$el = $page->find('css', '.modal.in');
		$name = $el->find('xpath', '//*[@id="crate_input_name"]');
		$desc = $el->find('xpath', '//*[@id="crate_input_description"]');
		if (strlen($name->getText()) > 0)
		{
			throw new Exception('Crate name is not empty');
		}
		if (strlen($desc->getText()) > 0)
		{
			throw new Exception('Crate description is not empty');
		}
		$name_validation = $el->find('xpath', '//*[@id="crate_name_validation_error"]');
		if (strlen($name_validation->getText()) > 0)
		{
			throw new Exception('Name validation error message is not empty');
		}
		$desc_validation = $el->find('xpath', '//*[@id="crate_description_validation_error"]');
		if (strlen($desc_validation->getText()) > 0)
		{
			throw new Exception('Description validation error message is not empty');
		}
		$error = $el->find('xpath', '//*[@id="create_crate_error"]');
		if (strlen($error->getText()) > 0)
		{
			throw new Exception('General error message is not empty');
		}
    }

    public function spin ($lambda, $wait = 15) {
        for ($i = 0; $i < $wait; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }
            sleep(1);
        }
        $backtrace = debug_backtrace();

        throw new Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . " (" . implode(',', $backtrace[1]['args']) .") \n" .
                (isset($backtrace[1]['file']) ? $backtrace[1]['file'] : '<unknown>') . ", line " .
                (isset($backtrace[1]['line']) ? $backtrace[1]['line'] : '<unknown>')
        );
    }

    /**
     * @Then /^I click by id "([^"]*)" using spin$/
     */
    public function iClickByIdUsingSpin($id) {
        $this->spin(function($context) use ($id) {
            $context->getSession()->getPage()->findById($id)->click();
            return true;
        });
    }


    public function waitForPageToLoad($timeout=10) {
        $timeout = $timeout * 1000000; // convert seconds to microseconds
        $increment = 250000; // 250ms
        $session = $this->getSession();
        for($i = 0; $i <= $timeout; $i += $increment) {
            $ready = $session->evaluateScript('return window.document.readyState == "complete"');
            $jquery = $session->evaluateScript('return 0 === $.active');
            if ($ready  and $jquery) {
                return;
            }
            usleep($increment);
        }
        throw new Exception('Page not ready after '.$timeout.' seconds');
    }

}

