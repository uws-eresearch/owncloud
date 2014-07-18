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
    private $bagmanager;
    
    public function __construct($api, $bagmanager){
        $this->api = $api;
        $this->bagmanager = $bagmanager;
    }
    
    public function addToBag($file)
    {
        return $this->bagmanager->addToBag($file);   
    }
    
}
