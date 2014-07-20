<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class CrateController extends Controller {
    
    /**
     * @var CrateService
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
    public function create()
    {
        if (!$this->params) {
            return new JSONResponse (
                array ('msg' => 'Params null'),
                501
            );
        }
        $crate_name = $this->params('crate_name');
        $crate_description = $this->params('crate_description');
        try {
            $msg = $this->$crateService->createCrate($crate_name, $crate_description);
            return new JSONResponse (array('msg' => $msg), 200);
        } catch (Exception $e) {
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
        \OCP\Util::writeLog('crate_it', "CrateController::get_items()", 3);
        try {
            \OCP\Util::writeLog('crate_it', "Selected Crate:".$_SESSION['selected_crate'], 3);
            $data = $this->crateService->getItems($_SESSION['selected_crate']);
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
        \OCP\Util::writeLog('crate_it', "CrateController::add()", 3);
        try
        {
            // TODO error handling
            $file = $this->params('file');
            \OCP\Util::writeLog('crate_it', "Adding ".$file, 3);
            $msg = $this->crate_service->addToBag($file);
            return new JSONResponse (array('msg'=>$msg), 200);
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
        $success = $this->crate_service->getManifest();
        return new JSONResponse (array('msg'=>'OK'), $success);
    }
    
    
    
}
