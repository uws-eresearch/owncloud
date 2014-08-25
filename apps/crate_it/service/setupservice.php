<?php

namespace OCA\crate_it\Service;

class SetupService {
    
    /**
     * @var API
     */
    private $api;
    
    /**
     * @var ConfigManager
     */
    private $config_manager;
    
    /**
     * @var CrateManager
     */
    private $crate_manager;
    
    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct($api, $config_manager, $crate_manager, $publisher){
        $this->api = $api;
        $this->config_manager = $config_manager;
        $this->crate_manager = $crate_manager;
        $this->publisher = $publisher;
    }
    
    // TODO: much of this could be pushed to javascript side and juse
    //       be loaded with the manifest
    public function loadParams() {
        $params = $this->loadConfigParams();
        if(!isset($_SESSION['selected_crate'])) {
            $_SESSION['selected_crate'] = 'default_crate';
        }
        $selectedCrate = $_SESSION['selected_crate']; // set by the CrateManager
        $manifestData = $this->crate_manager->getManifestData($selectedCrate);
        $params['creators']  = empty($manifestData['creators'])? array() : array_values($manifestData['creators']);
        $params['activities']  = empty($manifestData['activities'])? array() : array_values($manifestData['activities']);
        $params['description'] = $manifestData['description'];        
        $params['selected_crate'] = $selectedCrate;
        $params['crates'] = $this->crate_manager->getCrateList();        
        $params['bagged_files'] = $this->crate_manager->getCrateFiles($selectedCrate);
        $git = array();
        // TODO: Paramaterise the git repo
        exec('git --git-dir=/home/devel/owncloud/.git --work-tree=/home/devel/owncloud describe --tags', $git);
        $git = explode('-', $git[0]);
        $params['release'] = $git[0];
        $params['commit'] = $git[2];
        return $params;
    }
    
    /**
     * Create default crate if there are no crates
     * throws exception when fails
     */
    public function createDefaultCrate()
    {
        // TODO: I don't think this method is used anymore
        \OCP\Util::writeLog('crate_it', "Creaeting or getting default crate", 3);
        $this->crate_manager->createCrate("default_crate", "");
    }

    /**
     * Read from cr8it config file and load up params
     */
    private function loadConfigParams()
    {
        $params = array();
        $config = $this->config_manager->readConfig();  
        // init values
        $description_length = empty($config['description_length']) ? 6000 : $config['description_length'];
        $max_sword_mb = empty($config['max_sword_mb']) ? 0 : $config['max_sword_mb'];
        $max_zip_mb = empty($config['max_zip_mb']) ? 0 : $config['max_zip_mb'];
        // load up array
        $params['description_length'] = $description_length;
        $params['max_sword_mb'] = $max_sword_mb;
        $params['max_zip_mb'] = $max_zip_mb;
        $sword = $config['sword'];
        $params['sword_status'] = $sword['status'];
        // TODO: this is really ugly, check if collections can be made config parameters
        //       also, this slows down page load as it has to connect to another server
        //       (NOTE: the setup service gets called multiple times with each page load too!!)
        //       try memoizing the results or make it an ajax call
        if($sword['status'] == 'enabled') {
            $params['collections'] = $this->publisher->getCollections();
        }
        return $params;
    }

}
