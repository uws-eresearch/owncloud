<?php

namespace OCA\crate_it\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use \OCA\crate_it\Controller\PageController;
use \OCA\crate_it\Controller\CrateController;
use \OCA\crate_it\Controller\SearchController;
use \OCA\crate_it\Service\CrateService;
use \OCA\crate_it\Service\SetupService;
use \OCA\crate_it\Manager\CrateManager;
use \OCA\crate_it\Manager\ConfigManager;


class DIContainer extends BaseContainer {

    public function __construct() {
        parent::__construct('crate_it');

        // use this to specify the template directory
        $this['TwigTemplateDirectory'] = __DIR__ . '/../templates';
 
        /* Managers */
               
        $this['CrateManager'] = function($c) {
            return new CrateManager($c['API']);
        };
        
        $this['ConfigManager'] = function($c) {
            return new ConfigManager();  
        };
        
        /* Services */

        $this['SetupService'] = function($c) {
            return new SetupService($c['API'], $c['ConfigManager'], $c['CrateManager']);  
        };

        $this['CrateService'] = function($c) {
            return new CrateService($c['API'], $c['CrateManager']);  
        };
        
        /* Controllers */
                               
        $this['PageController'] = function($c) {
            return new PageController($c['API'], $c['Request'], $c['SetupService']);
        };
               
        $this['CrateController'] = function($c) {
            return new CrateController($c['API'], $c['Request'], $c['CrateService'], $c['SetupService']);
        };

        $this['SearchController'] = function($c) {
            return new SearchController($c['API'], $c['Request'], $c['ConfigManager']);
        };

    }

}