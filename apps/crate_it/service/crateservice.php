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
    
    // TODO: settle on either crateName or crateId and be consistant
    // TODO: tidy up the message return types to be consistent
    // TODO: this class doesn't seem to do anything, could
    //       we remove this class altogether and just use CrateManager?
    public function addToBag($crateId, $file) {
        return $this->crateManager->addToCrate($crateId, $file);   
    }
    
    public function getItems($crateId) {
        return $this->crateManager->getManifestData($crateId);
    }

    public function createCrate($crateName, $description) {
        return $this->crateManager->createCrate($crateName, $description);   
    }
    
    public function getCrateSize($crateId) {
        return $this->crateManager->getCrateSize($crateId);
    }
    
    public function updateCrate($crateId, $field, $value) {
        return $this->crateManager->updateCrate($crateId, $field, $value);
    }

    public function deleteCrate($crateName) {
        return $this->crateManager->deleteCrate($crateName);
    }

    public function renameCrate($crateName, $newCrateName) {
        return $this->crateManager->renameCrate($crateName, $newCrateName);
    }
    
    public function checkCrate($crateName) {
        return $this->crateManager->checkCrate($crateName);
    }
}
