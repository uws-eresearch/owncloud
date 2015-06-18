<?php

namespace OCA\crate_it\Controller;

use \OCA\crate_it\lib\SwordConnector;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http;

class PublishController extends Controller {

    private $publisher;
    private $crateManager;
    private $loggingService;
    private $mailer;

    public function __construct($appName, $request, $crateManager, $setupService, $publisher, $loggingService, $mailer) {
        parent::__construct($appName, $request);
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
        $params = $setupService->getParams();
        $endpoints = $params['publish endpoints']['sword'];
        \OCP\Util::writeLog('crate_it', "PublishController::construct() - Publish enpoints: $endpoints", \OCP\Util::DEBUG);
        $publisher->setEndpoints($params['publish endpoints']['sword']);
        $this->loggingService = $loggingService;
        $this->mailer = $mailer;
    }

    public function getCollections() {
      \OCP\Util::writeLog('crate_it', "PublishController::getCollections()", \OCP\Util::DEBUG);
      return $this->publisher->getCollections();
    }

    /**
     * Email crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function emailReceipt() {
        $data = array();
        if(!empty($_SESSION['last_published_status'])) {
            $to = $this->params('address');
            // TODO: This should be configurable
            $from = 'no-reply@cr8it.app';
            $subject = 'Cr8it Publish Status Receipt';
            try {
                $content = $this->loggingService->getLog();
                if($this->mailer->send($to, $from, $subject, $content)) {
                    $data['msg'] = "Publish log sent to $to";
                    $status = 200;
                } else {
                    throw new \Exception('Unable to send email at this time');
                }
            } catch(\Exception $e) {
                $data['msg'] = 'Error: '.$e->getMessage();
                $status = 500;
            }
        } else {
            $data['msg'] = 'Error: No recently published crates';
            $status = 500; // NOTE: should this be in the 400 range?
        }
        return new JSONResponse($data, $status);
    }

    /**
     * Publish crate
     *
     * @Ajax
     * @NoAdminRequired
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
                $data['msg'] = "Crate '$crateName' successfully published to $collection";  
                $this->loggingService->logPublishedDetails($package, $crateName);             
            } else {
                $this->loggingService->log("Publishing crate '$crateName' failed.");
                $data['msg'] = "Error: failed to publish crate '$crateName' to $collection: $response->sac_statusmessage ($status)";
                $this->loggingService->log($data['msg']);
            }
        } catch (\Exception $e) {
            $this->loggingService->log("Publishing crate '$crateName' failed.");            
            $status = 500;
            $data['msg'] = "Error: failed to publish crate '$crateName' to $collection: ".$e->getMessage();
            $this->loggingService->log($data['msg']);
        }
        $_SESSION['last_published_status'] = $data['msg'];

        return new JSONResponse($data, $status);
    }


    public function publishAlert() {
        // TODO
    }

}
