<?php

namespace OCA\crate_it\Service;

class CrateService {
    
    /**
     * @var API
     */
    private $api;
    
    /**
     * @var CrateManager
     */
    private $crate_manager;
    
    public function __construct($api, $crate_manager){
        $this->api = $api;
        $this->crate_manager = $crate_manager;
    }
    
    public function addToBag($file)
    {
        return $this->crate_manager->addToCrate($file);   
    }
    
    public function getItems($crate_id)
    {
        return $this->crate_manager->getManifestData($crate_id);
    }
    
}
