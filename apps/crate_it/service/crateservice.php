<?php

namespace OCA\crate_it\Service;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class CrateService {
    
    /**
     * @var API
     */
    private $api;
    
    /**
     * @var BagManager
     */
    private $bag_manager;
    
    /**
     * @var CrateManager
     */
    private $crate_manager;
    
    public function __construct($api, $bag_manager, $crate_manager){
        $this->api = $api;
        $this->bag_manager = $bag_manager;
        $this->crate_manager = $crate_manager;
    }
    
    public function addToBag($file)
    {
        return $this->bag_manager->addToBag($file);   
    }
    
}
