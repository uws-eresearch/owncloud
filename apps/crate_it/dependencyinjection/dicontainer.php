<?php

namespace OCA\crate_it\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

/*
use \OCA\crate_it\Controller\MyController;
use \OCA\crate_it\Service\MyService;
use \OCA\crate_it\Manager\MyManager;
*/
use \OCA\crate_it\Controller\PageController;
use \OCA\crate_it\Controller\CrateController;
use \OCA\crate_it\Service\CrateService;
use \OCA\crate_it\Manager\BagManager;
use \OCA\crate_it\Manager\CrateManager;


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
        **/
 
        $this['BagManager'] = function($c) {
            return new BagManager();  
        };
               
        $this['CrateManager'] = function($c) {
            return new CrateManager($c['API']);
        };
        
        $this['PageController'] = function($c) {
            return new PageController($c['API'], $c['Request'], $c['CrateManager']);
        };

        $this['CrateService'] = function($c) {
            return new CrateService($c['API'], $c['Request'], $c['BagManager'], $c['CrateManager']);  
        };
        
        $this['CrateController'] = function($c) {
            return new FileController($c['API'], $c['Request'], $c['CrateService']);
        };

    }

}