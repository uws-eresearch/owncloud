<?php

namespace OCA\crate_it\Service;



class SetupService {
    
    /**
     * @var CrateManager
     */
    private $crateManager;
    
    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @var configParams
     */
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
        $this->publisher->setEndpoints(self::$params['publish endpoints']['sword']);
        self::$params['collections'] = $this->publisher->getCollections();
        self::$params['crates'] = $this->crateManager->getCrateList();
        $manifestData = $this->crateManager->getManifestData($selectedCrate);
        self::$params['description'] = $manifestData['description'];  
        $this->getReleaseInfo();
    }
    
    private function getSelectedCrate() {
        if(!isset($_SESSION['selected_crate'])) {
            $_SESSION['selected_crate'] = 'default_crate';
        }
        return $_SESSION['selected_crate'];
    }

    private function getReleaseInfo() {
        $git = array();
        $params = self::$params['git'];
        exec("git --git-dir={$params['git-dir']} --work-tree={$params['work-tree']} describe --tags", $git);
        $git = explode('-', $git[0]);
        self::$params['release'] = $git[0];
        self::$params['commit'] = $git[2];
    }


    private function loadConfigParams() {
        $config = $this->readConfig();
        foreach($config as $key => $value) {
            \OCP\Util::writeLog('crate_it', "SetupService::loadConfigParams() - loading $key:$value", \OCP\Util::DEBUG);         
            
            self::$params[$key] = $value;
        }
    }

    private function readConfig() {
        $config = NULL;
        $configFile = \OC::$SERVERROOT . '/data/cr8it_config.json';
        if (file_exists($configFile)) {
          $config = json_decode(file_get_contents($configFile), true); // convert it to an array.
        }
        return $config;
    }

}
