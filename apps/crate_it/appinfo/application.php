<?php

namespace OCA\crate_it\AppInfo;

use \OCP\AppFramework\App;

use \OCA\crate_it\Controller\PageController;
use \OCA\crate_it\Controller\CrateController;
use \OCA\crate_it\Controller\SearchController;
use \OCA\crate_it\Controller\PublishController;
use \OCA\crate_it\Controller\CrateCheckController;
use \OCA\crate_it\Service\SetupService;
use \OCA\crate_it\Service\LoggingService;
use \OCA\crate_it\service\PublishingService;
use \OCA\crate_it\Manager\CrateManager;
use \OCA\crate_it\Manager\ConfigManager;


use \OCA\crate_it\lib\Mailer;
use \OCA\crate_it\lib\ZipDownloadResponse;

class Application extends App {
    
    public function __construct(array $urlParams=array()) {
        parent::__construct('crate_it', $urlParams);
        
        $container = $this->getContainer();
    
        /**
         * Controllers
         */
        $container->registerService('PageController', function($c){
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('SetupService')
            );
        });
        
        $container->registerService('CrateController', function($c){
            return new CrateController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('CrateManager')
            );
        });
        
        $container->registerService('CrateCheckController', function($c){
            return new CrateCheckController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('CrateManager'),
                    $c->query('LoggingService')
            );
        });
        
        $container->registerService('SearchController', function($c){
            return new SearchController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('SetupService')
            );
        });
        
        $container->registerService('PublishController', function($c){
            return new PublishController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('CrateManager'),
                    $c->query('SetupService'),
                    $c->query('PublishingService'),
                    $c->query('LoggingService'),
                    $c->query('Mailer')
            );
        });
        
        /**
         * Services
         */
        $container->registerService('SetupService', function($c){
            return new SetupService(
                $c->query('CrateManager'),
                $c->query('PublishingService')
            );
        });
        
        $container->registerService('CrateService', function($c){
            return new CrateService(
                    $c->query('CrateManager')
            );
        });
        
        $container->registerService('LoggingService', function($c){
            return new LoggingService(
                    $c->query('CrateManager')
            );
        });
        
        $container->registerService('Mailer', function($c){
            return new Mailer();
        });
        
        /**
         * Managers
         */
        $container->registerService('CrateManager', function($c){
            return new CrateManager();
        });
        
        /**
         * Connectors
         */
        $container->registerService('PublishingService', function($c){
            return new PublishingService();
        });
        
        /**
         * Core
         */
        $container->registerService('UserId', function($c) {
            return \OCP\User::getUser();
        });
        
    }
    
}