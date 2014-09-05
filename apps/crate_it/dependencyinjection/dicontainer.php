<?php

namespace OCA\crate_it\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use \OCA\crate_it\Controller\PageController;
use \OCA\crate_it\Controller\CrateController;
use \OCA\crate_it\Controller\SearchController;
use \OCA\crate_it\Controller\PublishController;
use \OCA\crate_it\Controller\CrateCheckController;
use \OCA\crate_it\Service\CrateService;
use \OCA\crate_it\Service\SetupService;
use \OCA\crate_it\Service\LoggingService;
use \OCA\crate_it\Manager\CrateManager;
use \OCA\crate_it\Manager\ConfigManager;

require 'lib/sword_connector.php';
use \OCA\crate_it\lib\SwordConnector;

require 'lib/zipdownloadresponse.php';

class DIContainer extends BaseContainer {

    public function __construct() {
        parent::__construct('crate_it');

        // use this to specify the template directory
        $this['TwigTemplateDirectory'] = __DIR__ . '/../templates';
 
        /* Managers */
               
        $this['CrateManager'] = function($c) {
            return new CrateManager($c['API'], $c['Twig']);
        };
        
        /* Connectors */

        $this['SwordConnector'] = function($c) {
            return new SwordConnector();
        };

        /* Services */

        $this['SetupService'] = function($c) {
            return new SetupService($c['CrateManager'], $c['SwordConnector']);  
        };

        $this['CrateService'] = function($c) {
            return new CrateService($c['API'], $c['CrateManager']);  
        };

        $this['LoggingService'] = function($c) {
            return new LoggingService($c['API'], $c['CrateManager']);  
        };
        
        /* Controllers */
                               
        $this['PageController'] = function($c) {
            return new PageController($c['API'], $c['Request'], $c['SetupService']);
        };
               
        $this['CrateController'] = function($c) {
            return new CrateController($c['API'], $c['Request'], $c['CrateService'], $c['SetupService']);
        };

        $this['SearchController'] = function($c) {
            return new SearchController($c['API'], $c['Request'], $c['SetupService']);
        };
        

        $this['PublishController'] = function($c) {
            return new PublishController($c['API'], $c['Request'], $c['CrateManager'], $c['SetupService'], $c['SwordConnector'], $c['LoggingService']);
        };
        
        $this['CrateCheckController'] = function($c) {
            return new CrateCheckController($c['API'], $c['Request'], $c['CrateService'], $c['LoggingService']);
        };
    }

}