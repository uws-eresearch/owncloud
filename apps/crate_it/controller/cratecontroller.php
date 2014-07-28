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
    public function createCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::create()", \OCP\Util::DEBUG);
        $name = $this->params('name');
        $description = $this->params('description');
        try {
            // TODO: maybe this selection stuff should be in a switchcrate method
            $msg = $this->crate_service->createCrate($name, $description);
            $_SESSION['selected_crate'] = $name;
            session_commit();
            return new JSONResponse(array('crateName' => $msg), 200);
        } catch (Exception $e) {
            return new JSONResponse (
                array ($e->getMessage(), 'error' => $e),
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
    public function getItems()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::get_items()", \OCP\Util::DEBUG);
        try {
            $crateName = $this->params('crate_id');
            $_SESSION['selected_crate'] = $crateName;
            session_commit();
            \OCP\Util::writeLog('crate_it', "selected_crate:: ".$_SESSION['selected_crate'], \OCP\Util::DEBUG);
            $data = $this->crate_service->getItems($crateName);
            return new JSONResponse($data, 200);
        } catch (Exception $e) {
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
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateSize()", \OCP\Util::DEBUG);
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
        \OCP\Util::writeLog('crate_it', "CrateController::updateCrate()", \OCP\Util::DEBUG);
        $field = $this->params('field');
        $value = $this->params('value');
        $msg = $this->crate_service->updateCrate($_SESSION['selected_crate'], $field, $value);
        return new JSONResponse($msg, 200);
    }

    /**
     * Delete Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function deleteCrate() {
        // TODO: all of these methods always return successfully, which shouldn't happen
        //       perhaps messages and response codes should be created by the CrateService?
        \OCP\Util::writeLog('crate_it', "CrateController::deleteCrate()", \OCP\Util::DEBUG);
        $this->crate_service->deleteCrate($_SESSION['selected_crate']);
        return new JSONResponse($data, 200);
    }
}
