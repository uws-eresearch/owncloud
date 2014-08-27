<?php

namespace OCA\crate_it\Service;



class SetupService {
    
    /**
     * @var API
     */
    private $api;
    
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


    // TODO load all the params into an array 
    // TODO: is $api ever used?
    public function __construct($api, $crateManager, $publisher){
        $this->api = $api;
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
    }
    

    public function getParams() {
        if(!self::$loaded) {
            $this->loadParams();
        }
        return self::$params;
    }

    // TODO: much of this could be pushed to javascript side and just
    //       be loaded with the manifest
    private function loadParams() {
        $this->loadConfigParams();
        $selectedCrate = $this->getSelectedCrate();
        self::$params['selected_crate'] = $selectedCrate;
        self::$params['collections'] = $this->publisher->getCollections();
        self::$params['crates'] = $this->crateManager->getCrateList();
    }
    
    private function getSelectedCrate() {
        if(!isset($_SESSION['selected_crate'])) {
            $_SESSION['selected_crate'] = 'default_crate';
        }
        return $_SESSION['selected_crate'];
    }

    private function getReleaseInfo() {
        $git = array();
        // TODO: Paramaterise the git repo
        // exec('git --git-dir=/home/devel/owncloud/.git --work-tree=/home/devel/owncloud describe --tags', $git);
        // $git = explode('-', $git[0]);
        // $params['release'] = $git[0];
        // $params['commit'] = $git[2];
    }


    /**
     * Read from cr8it config file and load up params
     */
    private function loadConfigParams() {
        $config = $this->readConfig();
        foreach($config as $key => $value) {
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
