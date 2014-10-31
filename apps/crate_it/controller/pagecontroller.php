<?php
namespace OCA\crate_it\Controller;

use \OCP\AppFramework\Controller; 
use \OCP\AppFramework\Http\TemplateResponse; 

class PageController extends Controller {
    
    
    /**
     * @var SetupService
     */
    private $setup_service;

    public function __construct($appName, IRequest $request, $setup_service) {
        parent::__construct($appName, $request);
        $this->setup_service = $setup_service;
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
            $model = $this->setup_service->getParams();
            
            //return $this->render('index', $model);
            return new TemplateResponse('crate_it', 'index', $model);
            
        } catch (\Exception $e) {
            // TODO handle exception
            \OCP\Util::writeLog('crate_it', "ERROR: " .$e->getMessage(), \OCP\Util::DEBUG);
        }
        
    }
    
    public function doEcho($echo) {
    	return array('echo' => $echo);
    }

}
