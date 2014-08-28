<?php

namespace OCA\crate_it\Service;

class LoggingService {
    
    /**
     * @var CrateManager
     */
    private $crateManager;
    
    public function __construct($crateManager) {
        $this->crate_manager = $crate_manager;
    }
    
    public function log($text) {
        
    }
    
    public function logManifest($crateName) {
        
    }
    
    public function logPackageStructure($zip) {
        
    }

    public function logPublishedDetails($crateName) {
        
    }
    
}
