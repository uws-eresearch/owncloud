<?php

namespace OCA\crate_it\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use \OCA\crate_it\Controller\MyController;
use \OCA\crate_it\Service\MyService;
use \OCA\crate_it\Manager\MyManager;

use \OCA\crate_it\Controller\PageController;

use \OCA\crate_it\Controller\CrateController;
use \OCA\crate_it\Service\CrateService;
use \OCA\crate_it\Manager\BagManager;


class DIContainer extends BaseContainer {

    public function __construct() {
        parent::__construct('crate_it');

        // use this to specify the template directory
        $this['TwigTemplateDirectory'] = __DIR__ . '/../templates';
/** 
        $this['MyManager'] = function($c) {
            return new MyManager();
        };       
        
        $this['MyService'] = function($c) {
            return new MyService($c['API'], $c['MyManager']);
        };
        
        $this['MyController'] = function($c) {
            return new MyController($c['API'], $c['Request'], $c['MyService']);
        };
 
        $this['BagItManager'] = function($c) {
            return \OCA\crate_it\lib\BagItManager::getInstance();
        };
         
        $this['CrateService'] = function($c) {
            return new CrateService($c['API'], $c['BagItManager']);
        };
                
        $this['CrateController'] = function($c) {
            return new CrateController($c['API'], $c['Request'], $c['CrateService']);  
        };
        
**/
        $this['PageController'] = function($c) {
            return new PageController($c['API'], $c['Request']);
        };
        
        $this['BagManager'] = function($c) {
            return new BagManager();  
        };
        
        $this['CrateService'] = function($c) {
            return new CrateService($c['API'], $c['Request'], $c['BagManager']);  
        };
        
        $this['CrateController'] = function($c) {
            return new FileController($c['API'], $c['Request'], $c['CrateService']);
        };

    }

}