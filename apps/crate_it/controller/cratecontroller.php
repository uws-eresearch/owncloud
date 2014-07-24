<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class CrateController extends Controller {
    
    /**
     * @var $crate_service
     */
    private $crate_service;
    
    public function __construct($api, $request, $crate_service) {
        parent::__construct($api, $request);
        $this->crate_service = $crate_service;
    }
    
    /**
     * Create crate with name and description
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function create() {
        \OCP\Util::writeLog('crate_it', "CrateController::create()", \OCP\Util::DEBUG);
        $name = $this->params('name');
        $description = $this->params('description');
        try {
            $msg = $this->crate_service->createCrate($name, $description);
            $_SESSION['selected_crate'] = $name;
            return new JSONResponse(array('msg' => $msg), 200);
        } catch (Exception $e) {
            \OCP\Util::writeLog('crate_it', $e->getMessage(), 3);
            return new JSONResponse (
                array ('msg' => $e->getMessage(), 'error' => $e),
                $e->getCode()
            );
        }
    }
    
    /**
     * Get crate items
     * 
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function get_items()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::get_items()", \OCP\Util::DEBUG);
        try {
            $crateName = $this->params('crate_id');
            $crateName = $crateName !== '' ? $crateName : 'default_crate'; // TODO delete this hack
            // $data = $this->crate_service->getItems($_SESSION['selected_crate']);
            $data = $this->crate_service->getItems($crateName);
            return new JSONResponse($data, 200);
        } catch (Exception $e)
        {
            return new JSONResponse(
                array('msg' => "Error getting manifest data", 'error' => $e),
                $e->getCode()
            );
        }
    }
    
    
    /**
     * Add To Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function add()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::add()", \OCP\Util::DEBUG);
        try
        {
            // TODO check if this error handling works
            $file = $this->params('file');
            \OCP\Util::writeLog('crate_it', "Adding ".$file, 3);
            $msg = $this->crate_service->addToBag($_SESSION['selected_crate'], $file);
            return new JSONResponse ($msg, 200);
        } catch(Exception $e)
        {
            return new JSONResponse(
                array('msg' => "Error adding file", 'error' => $e),
                $e->getCode()
            );
        }
       
    }
    
    /**
     * Get Crate Manifest
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function manifest()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::manifest()", \OCP\Util::DEBUG);
        $success = $this->crate_service->getManifest();
        return new JSONResponse (array('msg'=>'OK'), $success);
    }
    
    /**
     * Get Crate Size
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getCrateSize()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateSize()", 3);
        $data = $this->crate_service->getCrateSize($_SESSION['selected_crate']);
        return new JSONResponse($data, 200);
    }
    
    /**
     * Update Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function updateCrate()
    {
        $data = $this->params('vfs');
        $msg = $this->crate_service->updateCrate($_SESSION['selected_crate'], $data);
        return new JSONResponse($msg, 200);
    }
    
    /**
     * SwitchCrate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function switchCrate()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::switchCrates()", \OCP\Util::DEBUG);
        // TODO: setting session variables is horrible, see if we can avoid this altogether
        // TODO: symphony/twig don't use the session variable like this
        $session = $this->get('session');
        $session->set('selected_crate', $this->params('crate_id'));
        $this->get_items();

    }

    
}
