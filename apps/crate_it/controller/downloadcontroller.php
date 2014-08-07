<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\Response;
require 'apps/crate_it/lib/zipdownloadresponse.php';
use OCA\crate_it\lib\ZipDownloadResponse;

class DownloadController extends Controller {
    
    
    /**
     * @var $downloadService
     */
    private $downloadService;
    
    public function __construct($api, $request, $downloadService) {
        parent::__construct($api, $request);
        $this->downloadService = $downloadService;
    }
    
    /**
     * Download Zip
     *
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function downloadZip() {
        $zipfile = $this->downloadService->prepareZipFile();
        return new ZipDownloadResponse($zipfile, 'crate.zip');     
        //return readfile($zipfile);   
    }
    
    /**
     * Download ePub
     *
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function downloadEpub() {
        
    }
}