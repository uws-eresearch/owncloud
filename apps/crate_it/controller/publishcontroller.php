<?php

namespace OCA\crate_it\Controller;

use \OCA\crate_it\lib\SwordConnector;
use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class PublishController extends Controller {

    /**
     * @var $publisher
     */
    private $publisher;

    /**
     * @var crateManager
     */
    private $crateManager;
    
    /**
     * @var loggingService
     */
    private $loggingService;    

    public function __construct($api, $request, $crateManager, $setupService, $publisher, $loggingService) {
        parent::__construct($api, $request);
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
        $params = $setupService->getParams();
        $publisher->setEndpoints($params['publish endpoints']['sword']);
        $this->loggingService = $loggingService;
    }

    public function getCollections() {
      \OCP\Util::writeLog('crate_it', "PublishController::getCollections()", \OCP\Util::DEBUG);
      return $this->publisher->getCollections();
    }

    /**
     * Publish crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function publishCrate() {
        \OCP\Util::writeLog('crate_it', "PublishController::publishCrate()", \OCP\Util::DEBUG);
        $crateName = $this->params('name');
        $endpoint = $this->params('endpoint');
        $collection = $this->params('collection');
        $this->loggingService->log("Attempting to publish crate $crateName to collection: $collection");
        $this->loggingService->logManifest($crateName);
        $package = $this->crateManager->packageCrate($crateName);
        $zipname = basename($package);
        $this->loggingService->log("Zipped content into '$zipname'");
        $data = array();
        try {
            $this->loggingService->log("Publishing crate $crateName ($zipname)..");
            $response = $this->publisher->publishCrate($package, $endpoint, $collection);
            $status = $response->sac_status;
            if($status == 201) {                
                $data['msg'] = "$crateName successfully published to $collection";  
                $this->loggingService->log($data['msg']);
                $this->loggingService->logPublishedDetails($package, $crateName);             
            } else {
                $data['msg'] = "Error: $response->sac_statusmessage ($status)";
                 $this->loggingService->log($data['msg']);
            }
        } catch (\Exception $e) {
            $status = 500;
            $data['msg'] = 'Error: '.$e->getMessage();
            $this->loggingService->log($data['msg']);
        }
        return new JSONResponse($data, $status);
    }

}
