<?php

namespace OCA\crate_it\Service;

use OCA\crate_it\lib\Util;

class SetupService {
    
    private $crateManager;
    
    private $publisher;

    // TODO add other defaults here
    private static $params = array('description_length' => 6000, 'max_sword_mb' => 0, 'max_zip_mb' => 0,);

    private static $loaded = false;

    public function __construct($crateManager, $publisher){
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
    }
    

    public function getParams() {
        if(!self::$loaded) {
            $this->loadParams();
        }
        return self::$params;
    }


    private function loadParams() {
        $this->loadConfigParams();
        $selectedCrate = $this->getSelectedCrate();
        self::$params['selected_crate'] = $selectedCrate;
        $this->publisher->registerPublishers(self::$params['publish endpoints']);
        self::$params['collections'] = $this->publisher->getCollections();
        self::$params['crates'] = $this->crateManager->getCrateList();
        $manifestData = $this->crateManager->getManifest($selectedCrate);
        self::$params['description'] = $manifestData['description'];
        self::$params['data_retention_period'] = $manifestData['data_retention_period'];
        self::$params['embargo_enabled'] = $manifestData['embargo_enabled'];
        self::$params['embargo_date'] = $manifestData['embargo_date'];
        self::$params['embargo_details'] = $manifestData['embargo_details'];
        $info = \OC_App::getAppInfo('crate_it');
        self::$params['version'] = $info['version'];
    }
    
    
    private function getSelectedCrate() {
        if(!isset($_SESSION['selected_crate'])) {
            $_SESSION['selected_crate'] = 'default_crate';
        }
        return $_SESSION['selected_crate'];
    }


    private function loadConfigParams() {
        $config = Util::getConfig();
        // TODO: No error handling if config is null
        foreach($config as $key => $value) {
            \OCP\Util::writeLog('crate_it', "SetupService::loadConfigParams() - loading $key:".json_encode($value), \OCP\Util::DEBUG);
            self::$params[$key] = $value;
        }
    }


}

