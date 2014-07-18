<?php
namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;

class PageController extends Controller {
    
    
    /**
     * @var CrateManager
     */
    private $crate_manager;

    public function __construct($api, $request, $crate_manager) {
        parent::__construct($api, $request);
        $this->crate_manager = $crate_manager;
    }

    /**
     * Home page index, displays default crate or last selected crate
     * 
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function index() {       
        \OCP\Util::writeLog('crate_it', "PageController::index()", 3);         
        $model = $this->set_up_params();
        return $this->render('index', $model);
    }

    private function set_up_params() {
        /**
        $manifestData = $bagit_manager->getManifestData();
        $config = $bagit_manager->getConfig();
        
        $description_length = empty($config['description_length']) ? 6000 : $config['description_length'];
        $max_sword_mb = empty($config['max_sword_mb']) ? 0 : $config['max_sword_mb'];
        $max_zip_mb = empty($config['max_zip_mb']) ? 0 : $config['max_zip_mb'];

        $model = array("previews" => $bagit_manager->showPreviews(),
                        "crates" => $bagit_manager->getCrateList(),
                        "selected_crate" => $bagit_manager->getSelectedCrate(),
                        "bagged_files" => $bagit_manager->getBaggedFiles(),
                        'description'=> $manifestData['description'],
                        "description_length" => $description_length, 
                        "max_sword_mb" => $max_sword_mb, 
                        "max_zip_mb" => $max_zip_mb, 
                        "mint_status" => $bagit_manager->getMintStatus(), 
                        "sword_status" => $bagit_manager->getSwordStatus(), 
                        "sword_collections" => $bagit_manager->getCollectionsList());
        $model['creators']  = empty($manifestData['creators'])? array() : array_values($manifestData['creators']);
        $model['activities']  = empty($manifestData['activities'])? array() : array_values($manifestData['activities']);
        **/
        $model = array();
        $model['crates'] = $this->crate_manager->getCrateList();           
        return $model;                          
    }

}
