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
    
    public function __construct($api, $config_manager, $crate_manager){
        $this->api = $api;
        $this->config_manager = $config_manager;
        $this->crate_manager = $crate_manager;
    }
    
    public function loadParams()
    {
        $params = $this->loadConfigParams();
        $params['crates'] = $this->crate_manager->getCrateList();
        return $params;
    }
    
    /**
     * Create default crate if there are no crates
     * throws exception when fails
     */
    public function createDefaultCrate()
    {
        \OCP\Util::writeLog('crate_it', "Creaeting or getting default crate", 3);
        $this->crate_manager->createCrate("default_crate");
    }
    
    public function getCrateFiles($crate_id)
    {
        return $this->crate_manager->getCrateFiles($crate_id);
    }
    
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
        return $params;
    }

}
