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
    
    // TODO: tidy up the message return types to be consistent
    // TODO: this class doesn't seem to do anything, could
    //       we remove this class altogether and just use CrateManager?
    // TODO: Make naming consistent addToBag vs addToCrate
    public function addToBag($crateName, $file) {
        return $this->crateManager->addToCrate($crateName, $file);   
    }
    
    // TODO: Rename to getManifest()
    public function getItems($crateName) {
        return $this->crateManager->getManifestData($crateName);
    }

    public function getCrateFiles($crateName) {
        return $this->crateManager->getCrateFiles($crateName);
    }

    public function getReadme($crateName) {
        return $this->crateManager->getReadme($crateName);   
    }

    public function createCrate($crateName, $description) {
        return $this->crateManager->createCrate($crateName, $description);   
    }
    
    public function getCrateSize($crateName) {
        return $this->crateManager->getCrateSize($crateName);
    }
    
    public function updateCrate($crateName, $field, $value) {
        return $this->crateManager->updateCrate($crateName, $field, $value);
    }

    public function deleteCrate($crateName) {
        return $this->crateManager->deleteCrate($crateName);
    }

    public function renameCrate($crateName, $newCrateName) {
        return $this->crateManager->renameCrate($crateName, $newCrateName);
    }
    
    public function packageCrate($crateName) {
        return $this->crateManager->packageCrate($crateName);
    }

    public function checkCrate($crateName) {
        return $this->crateManager->checkCrate($crateName);
    }

    public function generateEPUB($crateName) {
        return $this->crateManager->generateEPUB($crateName);
    }
}
