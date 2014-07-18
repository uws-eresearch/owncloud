<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class CrateController extends Controller {
    
    /**
     * @var CrateService
     */
    private $crateService;
    
    public function __construct($api, $request, $crateService) {
        parent::__construct($api, $request);
        $this->crateService = $crateService;
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
     * Add To Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function add()
    {
        $file = $this->params('file');
        $msg = $this->$crate_service->add($file);
        return new JSONResponse (array('msg'=>$msg), 200);
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
        $success = $this->$crate_service->getManifest();
        return new JSONResponse (array('msg'=>'OK'), $success);
    }
    
    
    
}
