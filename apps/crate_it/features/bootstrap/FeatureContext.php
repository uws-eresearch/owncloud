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
// require_once 'PHPUnit/Autoload.php';
// require_once 'PHPUnit/Framework/Assert/Functions.php';

// require_once '../../vendor/autoload.php';
require_once 'vendor/autoload.php';
require_once 'vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{

    private static $file_root = '/var/www/html/owncloud/data/test/files/';
    private static $crate_root = '/var/www/html/owncloud/data/test/crates/';
    private static $ssh_command = 'ssh -i ../../puphpet/files/dot/ssh/id_rsa -p 2222 root@127.0.0.1 ';

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
        $this->visit('/owncloud/index.php/apps/crate_it/');
        $this->waitForPageToLoad();
    }

    /**
     * @When /^I navigate to "([^"]*)"$/
     */
    public function iNavigateToFolder($folder)
    {
        $this->visit('/owncloud/index.php/apps/files?dir=/'.$folder);
        $this->waitForPageToLoad();
    }

    /**
     * @Given /^I go to the files page$/
     */
    public function iGoToTheFilesPage()
	{
		$this->visit('/owncloud/index.php/apps/files');
        $this->waitForPageToLoad();
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
    public function iHaveFolders($folders) {
        $command = 'mkdir -m 755 -p '.self::$file_root.$folders;
        $this->exec_sh_command($command);
    }

    /**
     * @Given /^I have file "([^"]*)" within "([^"]*)"$/
     */
    public function iHaveFileWithin($file, $folder)
    {
        $folder = (!empty($folder) ? $folder.'/' : $folder);
        $command = 'touch '.self::$file_root.$folder.$file;
        $this->exec_sh_command($command);
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
     * @When /^I add "([^"]*)" to the current crate$/
     */
    public function iAddToTheCurrentCrate($item) {
        $this->spin(function($context) use ($item) {
            $page = $context->getSession()->getPage();
            // $el = $page->find('xpath', '//tr[@data-file="' . $item. '"]//label')->click();
            $el = $page->find('xpath', '//tr[@data-file="' . $item. '"]//label');
            if (!$el->isVisible()) {
                throw new Exception('The element should be visible');
            }
            $el->click();
            $el = $page->find('xpath', '//tr[@data-file="' . $item. '"]//a[@data-action="Add to crate"]');
            if (!$el->isVisible()) {
                throw new Exception('The element should be visible');
            }
            $el->click();
            return true;	
        });
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
     * @Then /^"([^"]*)" should not be visible in the current crate$/
     */
    public function shouldNotBeVisibleInTheCurrentCrate($itemName) {
        $this->spin(function($context) use ($itemName) {
            $page = $context->getSession()->getPage();
            $web_assert = new WebAssert($context->getSession());
            $root_folder = $page->find('xpath', '//div[@id="files"]/ul/li');
            $element = $web_assert->elementExists('xpath','//ul/li/div/span[text()="'.$itemName. '"]', $root_folder);   
            if ($element->isVisible()) {
                throw new Exception('The element should not be visible');
            }
            return true;
        });
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
     * @Then /^"([^"]*)" should be visible in the current crate$/
     */
    public function shouldBeVisibleInTheCurrentCrate($itemName) {
        $this->spin(function($context) use ($itemName) {
            $page = $context->getSession()->getPage();
    		$web_assert = new WebAssert($context->getSession());
            $root_folder = $page->find('xpath', '//div[@id="files"]/ul/li');
            $element = $web_assert->elementExists('xpath','//ul/li/div/span[text()="'.$itemName. '"]', $root_folder);	
        	if (!$element->isVisible())	{
    			throw new Exception('The element should be visible');
    		}
            return true;
        });
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
    public function iHaveNoFiles() {
        $command = "rm -rf /var/www/html/owncloud/data/test/files/*";
        $this->exec_sh_command($command);
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
        $this->waitForPageToLoad();
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
        $this->spin(function($context) use ($actions, $crateItem) {
            $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]/following-sibling::ul';
            $page = $context->getSession()->getPage();
            $el = $page->find('xpath', $xpath);
            $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
            $actionItems = explode(', ', $actions);
            $web_assert = new WebAssert($context->getSession());
            foreach ($actionItems as $action) {
                $web_assert->elementExists('xpath', $xpath.'//a[text() = "'.$action.'"]');
            }
            return true;
        });
    }

    /**
     * @Given /^I should not have crate actions "([^"]*)" for "([^"]*)"$/
     */
    public function iShouldNotHaveCrateActionsFor($actions, $crateItem)
    {
        $this->spin(function($context) use ($actions, $crateItem) {
            $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]/following-sibling::ul';
            $page = $context->getSession()->getPage();
            $el = $page->find('xpath', $xpath);
            $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
            $actionItems = explode(', ', $actions);
            $web_assert = new WebAssert($context->getSession());
            foreach ($actionItems as $action) {
                $web_assert->elementNotExists('xpath', $xpath.'//a[text() = "'.$action.'"]');
            }
            return true;
        });
    }

    /**
     * Gets a file action element by crate item name font-awesome icon class
     **/
    private function performActionElByFAIcon($crateItem, $fa_icon_class) {
        $this->spin(function($context) use ($crateItem, $fa_icon_class) {
            $xpath = '//span[contains(concat(" ", normalize-space(@class), " "), " jqtree-title ") and text() = "'.$crateItem.'"]';
            $page = $context->getSession()->getPage();
            $el = $page->find('xpath', $xpath);
            if (!$el->isVisible()) {
                throw new Exception('The element should be visible');
            }
            $el->click(); // HACK: Click method moves the webdriver mouse, so CSS :hover elements display
            $xpath = '/following-sibling::ul//i[@class="fa '.$fa_icon_class.'"]';
            $el = $el->find('xpath', $xpath);
            if (!$el->isVisible()) {
                throw new Exception('The element should be visible');
            }
            $el->click();
            return true;
        });
    }
	

    /**
     * @Given /^I have crate "([^"]*)"$/
     */
    public function iHaveCrate($crateName) {
        // $mainfest = '{"description":"","creators":[],"activities":[],"vfs":[{"id":"rootfolder","name":"'.$crateName.'","folder":true,"children":[]}]}';
        $mainfest = '"{\"description\":\"\",\"creators\":[],\"activities\":[],\"vfs\":[{\"id\":\"rootfolder\",\"name\":\"'.$crateName.'\",\"folder\":true,\"children\":[]}]}"';
        $data_path = self::$crate_root.$crateName.'/data';
        $command = "mkdir -m 755 -p $data_path\\";
        $this->exec_sh_command($command);
        $command = "echo $mainfest | sudo tee $data_path/manifest.json";
        $this->exec_sh_command($command);
        $command = 'chown -R apache:apache '.self::$crate_root;
        $this->exec_sh_command($command);
    }

    /**
     * @Given /^I have no crates$/
     */
    public function iHaveNoCrates() {
        $command = 'rm -rf '.self::$crate_root;
        $this->exec_sh_command($command);
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
        $xpath = '//a[@id="create"]';
        $page->find('xpath', $xpath)->click();
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
        $value = str_repeat('a', $arg2);
		if (strlen($value) != $arg2)
		{
			throw new Exception('Repeat characters fail');
		}
		$page = $this->getSession()->getPage();
		$xpath = '//*[@id="'.$arg1.'"]';
        $desc = $page->find('xpath', $xpath);
		$desc->setValue($value);
    }
	
    /**
     * @Given /^the selected crate name should be a long string truncated to (\d+) characters$/
     */
    public function theSelectedCrateNameShouldBeALongStringTruncatedToCharacters($arg1)
    {
        $page = $this->getSession()->getPage();
    	$optionElement = $page->find('xpath', '//select[@id="crates"]/option[@selected]');		
    	$name_text =  (string)$optionElement->getText();
		$name_len = strlen($name_text);
		if ($name_len != $arg1)
		{
			throw new Exception('Crate name is not "'. $arg1 .'" characters long. It is ' .$name_len);
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
    }

    /**
     * @Given /^"([^"]*)" in the popup dialog should be diasbled$/
     */
    public function inThePopupDialogShouldBeDiasbled($buttonText)
    {
        $page = $this->getSession()->getPage();
        $el = $page->find('css', '.modal.in');
        // $el->find('xpath', '//button[text() = "'.$buttonText.'" and @disabled]');
        $web_assert = new WebAssert($this->getSession());
        $web_assert->elementExists('xpath', '//button[text() = "'.$buttonText.'" and @disabled]', $el);
    }

	
	public function grantNumberSectionCollapsed()
	{
		$page = $this->getSession()->getPage();
		$xpath = '//div[@id="grant-numbers"]';
		$el = $page->find('xpath', $xpath);
		return !$el->isVisible();
	}

    private function mockActivityLookup()
	{
        // TODO: Look at way to load mockjax dynamically so that is isn't loaded in production
        // NOTE: DO NOT INDENT THE FOLLOWING BLOCK - leave it how it is! 	
        $js = <<<JS
var result = '[' + 
  			      '{\"result-metadata\":{\"all\": {\"id\": [\"111123\"], \"grant_number\": [\"111123\"], \"dc_title\": [\"Title A\"], \"dc_date\": [\"1999\"]}}}'
  			       + ',' +
  			      '{\"result-metadata\":{\"all\": {\"id\": [\"123123\"], \"grant_number\": [\"123123\"], \"dc_title\": [\"Title B\"], \"dc_date\": [\"2010\"]}}}'
  			       + ',' +
  			      '{\"result-metadata\":{\"all\": {\"id\": [\"123456\"], \"grant_number\": [\"123456\"], \"dc_title\": [\"Title C\"], \"dc_date\": [\"1988\"]}}}'
  			    +']';			    
$.mockjax({
    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
    type: 'post',
    dataType: 'json',
    data: {
        'action': 'search',
        'keywords': '123'
      },
    responseText : result
  });
JS;
	   $this->getSession()->executeScript($js);
	   		
	}
	

    private function resultlessMockActivityLookup() {
                $js = <<<JS
$.mockjax({
    url: OC.linkTo('crate_it', 'ajax/bagit_handler.php'),
    type: 'post',
    dataType: 'json',
    data: {
        'action': 'search',
        'keywords': 'abc'
      },
    responseText: '[{\"result-metadata\":{\"all\": {\"id\": [\"111123\"], \"grant_number\": [\"111123\"], \"dc_title\": [\"Title A\"], \"dc_date\": [\"1999\"]}}}]'
  });
JS;
        $this->getSession()->executeScript($js);
    }

    /**
     * @Given /^I expand the grant number metadata section$/
     */
    public function iExpandTheGrantNumberMetadataSection()
    {
		$this->spin(function($context) {
		    if ($context->grantNumberSectionCollapsed())
			{
	    		$page = $context->getSession()->getPage();
				$xpath = '//a[@href="#grant-numbers"]/i';
		        $expand_trigger = $page->find('xpath', $xpath);
				$expand_trigger->click();
			}
			return true;
		});
    }
	
    /**
     * @Given /^I click the search grant number button$/
     */
    public function iClickTheSearchGrantNumberButton()
    {
    	$this->getSession()->executeScript('$.mockjaxClear();');	
	    $this->mockActivityLookup();		
		$this->spin(function($context) {	
	    	$session = $context->getSession();
	        $page = $session->getPage();
	        $xpath = '//button[@id="search_activity"]';
			$el = $page->find('xpath', $xpath);
			$el->click();
			return true;
		});
		sleep(1);
		// clear mockjax
		$this->getSession()->executeScript('$.mockjaxClear();');
    }
 

    /**
     * @Given /^I click the search grant number button and get no results$/
     */
    public function iClickTheSearchGrantNumberButtonAndGetNoResults()
    {
        $this->getSession()->executeScript('$.mockjaxClear();');
        $this->resultlessMockActivityLookup();
        $this->spin(function($context) {    
            $session = $context->getSession();
            $page = $session->getPage();
            $xpath = '//button[@id="search_activity"]';
            $el = $page->find('xpath', $xpath);
            $el->click();
            return true;
        });
        sleep(2);
        // clear mockjax
        $this->getSession()->executeScript('$.mockjaxClear();');
    }


    /**
     * @When /^I clear all activities$/
     */
    public function iClearAllActivities()
    {
        // TODO: A lot of these methods just search by xpath and click and element,
        // The can probably be refactored and remove to be a lot DRYer
        $this->spin(function($context) {
            $page = $context->getSession()->getPage();
            $button = $page->find('css', '#clear_grant_numbers');
            $button->click();
            return true;
        });
    }

    /**
     * @Then /^I should see these entries in the result list$/
     */
    public function iShouldSeeTheseEntriesInTheResultList(TableNode $table)
    {

		$page = $this->getSession()->getPage();
        $xpath = '//ul[@id="search_activity_results"]//p[@class="metadata_heading"]';
		$grants = $this->checkSearchResult($xpath, $page);
		
		$xpath = '//ul[@id="search_activity_results"]//p[2]';
		$years = $this->checkSearchResult($xpath, $page);
		
		$xpath = '//ul[@id="search_activity_results"]//p[3]';
		$titles = $this->checkSearchResult($xpath, $page);
		
        $hash = $table->getHash();
		for ($count = 0; $count < count($hash); $count++ ){
		   $this->matchTableValue($hash[$count]['grant'], $grants[$count], $count);
		   $this->matchTableValue($hash[$count]['year'], $years[$count], $count);
		   $this->matchTableValue($hash[$count]['title'], $titles[$count], $count);
		}
    }
    
	private function checkSearchResult($xpath, $page)
	{
		$el_array = $page->findAll('xpath', $xpath);
		if (empty($el_array))
		{
			throw new Exception('No results are returned: '.$xpath);
		}
		return $el_array;
	}
	
	private function matchTableValue($hashval, $el, $count)
	{
		$actual_val = $el->getText();
	    $expected_val = $hashval;
        if ($actual_val != $expected_val)
	    {
	 	   throw new Exception('Mismatch result ('.$count.'). Expected: '.$expected_val.', actual: '.$actual_val);
	    }
	}

    /**
     * @Given /^I add grant number "([^"]*)" to the list$/
     */
    public function iAddGrantNumberToTheList($arg1)
    {
    	$this->spin(function($context) use ($arg1) {
	        $page = $context->getSession()->getPage();
			$xpath = '//ul[@id="search_activity_results"]//button[@id="'.$arg1.'"]';
			$button = $page->find('xpath', $xpath);
			$button->click();
			return true;
		});
    }

	/**
     * @Given /^I remove grant number "([^"]*)" in the list$/
     */
    public function iRemoveGrantNumberInTheList($arg1)
    {
    	$this->spin(function($context) use ($arg1) {
	        $page = $context->getSession()->getPage();
			$xpath = '//ul[@id="selected_activities"]//button[@id="'.$arg1.'"]';
			$button = $page->find('xpath', $xpath);
			$button->click();
			return true;
		});
    }

	/**
     * @Given /^I select crate "([^"]*)"$/
     */
    public function iSelectCrate($arg1)
    {
    	$this->spin(function($context) use ($arg1) {
	        $page = $context->getSession()->getPage();
	    	$optionElement = $page->find('xpath', '//select[@id="crates"]');
			$optionElement->selectOption($arg1, false);	
			return true;
		});
        $this->waitForPageToLoad();
	}

        /**
     * @Then /^I should no selected grants$/
     */
    public function iShouldNoSelectedGrants()
    {
        $this->spin(function($context) {
            $page = $context->getSession()->getPage();
            $grants = $page->findAll('css', '#selected_activities > li');
            assertEquals(0, count($grants));
            return true;
        });
    }

    /**
     * @Then /^I should see these entries in the selected grant number list$/
     */
    public function iShouldSeeTheseEntriesInTheSelectedGrantNumberList(TableNode $table)
    {
    	sleep(1);
		$page = $this->getSession()->getPage();
		$xpath = '//ul[@id="selected_activities"]//p[@class="metadata_heading"]';
		$grants = $this->checkSearchResult($xpath, $page);
		
		$xpath = '//ul[@id="selected_activities"]//p[2]';
		$years = $this->checkSearchResult($xpath, $page);
		
		$xpath = '//ul[@id="selected_activities"]//p[3]';
		$titles = $this->checkSearchResult($xpath, $page);
		
        $hash = $table->getHash();
		for ($count = 0; $count < count($hash); $count++ ){
		   $this->matchTableValue($hash[$count]['grant'], $grants[$count], $count);
		   $this->matchTableValue($hash[$count]['year'], $years[$count], $count);
		   $this->matchTableValue($hash[$count]['title'], $titles[$count], $count);
		}
    }	

    public function spin($lambda, $timeout=10) {
        $timeout = $timeout * 1000000; // convert seconds to microseconds
        $increment = 50000; // 50ms
        for ($i = 0; $i < $timeout; $i += $increment) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }
            usleep($increment);
        }
        $backtrace = debug_backtrace();

        throw new Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . " (" . implode(',', $backtrace[1]['args']) .") \n" .
                (isset($backtrace[1]['file']) ? $backtrace[1]['file'] : '<unknown>') . ", line " .
                (isset($backtrace[1]['line']) ? $backtrace[1]['line'] : '<unknown>')
        );
    }
	
    public function waitForPageToLoad($timeout=10) {
        $timeout = $timeout * 1000000; // convert seconds to microseconds
        $increment = 50000; // 50ms
        $session = $this->getSession();
        for($i = 0; $i <= $timeout; $i += $increment) {
            $ready = $session->evaluateScript('return window.document.readyState == "complete"');
            $jquery = $session->evaluateScript('return $ != undefined && 0 === $.active');
            if ($ready and $jquery) {
                return;
            }
            usleep($increment);
        }
        throw new Exception('Page not ready after '.($timeout/1000000).' seconds');
    }

    private function exec_sh_command($command) {
        if(getenv('TEST_ENV') == 'vagrant') {
            $command = self::$ssh_command."'$command'";
        } else {
            $command = 'sudo '.$command;
        }
        exec($command);
    }

}

