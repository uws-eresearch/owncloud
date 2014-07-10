<?php

namespace OCA\crate_it\DependencyInjection;

use \OCA\AppFramework\DependencyInjection\DIContainer as BaseContainer;

use \OCA\crate_it\Controller\PageController;

class DIContainer extends BaseContainer {
	
    public function __construct(){
        parent::__construct('crate_it');

        // use this to specify the template directory
        $this['TemplateDirectory'] = __DIR__ . '/../templates';

        $this['PageController'] = function($c){
            return new PageController($c['API'], $c['Request']);
        };
    }

}