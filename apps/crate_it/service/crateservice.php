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
    private $crateManager;
    
    public function __construct($api, $crateManager){
        $this->api = $api;
        $this->crateManager = $crateManager;
    }
    
    public function addToBag($crateId, $file) {
        return $this->crateManager->addToCrate($crateId, $file);   
    }
    
    public function getItems($crateId) {
        return $this->crateManager->getManifestData($crateId);
    }

    public function createCrate($crateName, $description) {
        return $this->crateManager->createCrate($crateName, $description);   
    }
    
    public function getCrateSize($crateId)
    {
        return $this->crateManager->getCrateSize($crateId);
    }
    
    public function updateCrate($crateId, $field, $value)
    {
        return $this->crateManager->updateCrate($crateId, $field, $value);
    }
    
}
