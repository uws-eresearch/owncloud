<?php
namespace OCA\crate_it\Controller;

use \OCP\AppFramework\Controller;

// TODOL Perhaps fold this in with CrateController
class PageController extends Controller {
    
    private $setupService;

    public function __construct($appName, $request, $setupService) {
        parent::__construct($appName, $request);
        $this->setupService = $setupService;
    }



   /**
     * Home page index, displays default crate or last selected crate
     * 
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {       
        \OCP\Util::writeLog('crate_it', "PageController::index()", \OCP\Util::DEBUG);         
        try {
            $model = $this->setupService->getParams();
            return $this->render('index', $model);
        } catch (\Exception $e) {
            // TODO handle exception
            \OCP\Util::writeLog('crate_it', "ERROR: " .$e->getMessage(), \OCP\Util::DEBUG);
        }
        
    }

}
