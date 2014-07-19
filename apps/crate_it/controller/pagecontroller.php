<?php
namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;

class PageController extends Controller {
    
    
    /**
     * @var SetupService
     */
    private $setup_service;

    public function __construct($api, $request, $setup_service) {
        parent::__construct($api, $request);
        $this->setup_service = $setup_service;
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
        try {
            $this->create_default_crate();         
            $model = $this->set_up_params();
             \OCP\Util::writeLog('crate_it', $model, 3);
            return $this->render('index', $model);
            
        } catch (Exception $e) {
            // TODO handle exception
            \OCP\Util::writeLog('crate_it', "ERROR: " .$e->getMessage(), 3);         
        }
        
    }
    
    private function create_default_crate() {
        // create default crate if no crates are available, or
        // for some reason no crate is selected
        if ($this->session('selected_crate') == null) 
        {
            \OCP\Util::writeLog('crate_it', "No selected crate, creating default", 3);
            $this->setup_service->createDefaultCrate();
            // The session variable holds the current selected crate.
            // Make sure to update this whenever you change selected crate.
            // The above line should throw an exception if it fails so
            // the session variable is maintained
            $_SESSION['selected_crate'] = 'default_crate';
            session_commit();
             \OCP\Util::writeLog('crate_it', "Wrote to session: ".$this->session('selected_crate'), 3);
        }             
    }

    private function set_up_params() {
        /**
        $manifestData = $bagit_manager->getManifestData();                

        $model = array("previews" => $bagit_manager->showPreviews(),
                        "selected_crate" => $bagit_manager->getSelectedCrate(),
                        "bagged_files" => $bagit_manager->getBaggedFiles(),
                        'description'=> $manifestData['description'],
                        "mint_status" => $bagit_manager->getMintStatus(), 
                        "sword_status" => $bagit_manager->getSwordStatus(), 
                        "sword_collections" => $bagit_manager->getCollectionsList());
        $model['creators']  = empty($manifestData['creators'])? array() : array_values($manifestData['creators']);
        $model['activities']  = empty($manifestData['activities'])? array() : array_values($manifestData['activities']);
        **/
        $model = $this->setup_service->loadParams();  
        $selected_crate = $_SESSION['selected_crate'];
        $model['selected_crate'] = $selected_crate;  
        $model['bagged_files'] = $this->setup_service->getCrateFiles($selected_crate);  
        return $model;                          
    }

}
