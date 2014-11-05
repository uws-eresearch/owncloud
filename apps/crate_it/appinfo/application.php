<?php
/**
 * ownCloud - crate_it
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lloyd <lloyd@example.com>
 * @copyright Lloyd 2014
 */

namespace OCA\crate_it\AppInfo;

//require_once __DIR__ . '/autoload.php';

use \OCP\AppFramework\App;
use \OCP\IContainer;

use OCA\crate_it\Controller\PageController;
use OCA\crate_it\Controller\CrateController;
use OCA\crate_it\Controller\CrateCheckController;
use OCA\crate_it\Controller\SearchController;
use OCA\crate_it\Service\SetupService;
use OCA\crate_it\Service\CrateService;
use OCA\crate_it\Service\LoggingService;
use OCA\crate_it\Manager\CrateManager;

require __DIR__ . '/../lib/sword_connector.php'; //TODO should load all required files using autoload.php (composer)
use OCA\crate_it\lib\SwordConnector;

class Application extends App {
	
	public function __construct(array $urlParams=array()) {
		parent::__construct('crate_it', $urlParams);
		
		$container = $this->getContainer();
		
		/**
		 * Controllers
		 */
		$container->registerService('PageController', function(IContainer $c){
			return new PageController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('SetupService')
			);
		});
		
		$container->registerService('CrateController', function(IContainer $c){
			return new CrateController(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query('CrateService')
			);
		});
		
		$container->registerService('CrateCheckController', function(IContainer $c){
			return new CrateCheckController(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query('CrateService'),
					$c->query('LoggingService')
			);
		});
		
		$container->registerService('SearchController', function(IContainer $c){
			return new SearchController(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query('SetupService')
			);
		});
		
		/**
		 * Services
		 */
		$container->registerService('SetupService', function(IContainer $c){
			return new SetupService(
				$c->query('CrateManager'),
				$c->query('SwordConnector')
			);
		});
		
		$container->registerService('CrateService', function(IContainer $c){
			return new CrateService(
					$c->query('CrateManager')
			);
		});
		
		$container->registerService('LoggingService', function(IContainer $c){
			return new LoggingService(
					$c->query('UserId'),
					$c->query('CrateManager')
			);
		});
		
		/**
		 * Managers
		 */
		$container->registerService('CrateManager', function(IContainer $c){
			return new CrateManager();
		});
		
		/**
		 * Connectors
		 */
		$container->registerService('SwordConnector', function(IContainer $c){
			return new SwordConnector();
		});
		
		/**
		 * Core
		 */
		$container->registerService('UserId', function(IContainer $c) {
			return \OCP\User::getUser();
		});
		
	}
	
}