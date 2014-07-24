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
    
    public function getCrateSize($crate_id)
    {
        return $this->crateManager->getCrateSize($crate_id);
    }
    
    public function updateCrate($crate_id, $data)
    {
        return $this->crateManager->updateCrate($crate_id);
    }
    
}
