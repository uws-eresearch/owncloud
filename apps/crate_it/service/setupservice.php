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
    
    public function loadParams($crate_id)
    {
        $params = $this->loadConfigParams();
        $manifestData = $this->crate_manager->getManifestData($crate_id);
        $params['creators']  = empty($manifestData['creators'])? array() : array_values($manifestData['creators']);
        $params['activities']  = empty($manifestData['activities'])? array() : array_values($manifestData['activities']);
        $params['description'] = $manifestData['description'];        
        $model['selected_crate'] = $crate_id;  
        $params['crates'] = $this->crate_manager->getCrateList();        
        $model['bagged_files'] = $this->crate_manager->getCrateFiles($crate_id);
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
        return $params;
    }

}
