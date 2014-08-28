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

    public function __construct($api, $request, $crateManager, $setupService, $publisher) {
        parent::__construct($api, $request);
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
        $params = $setupService->getParams();
        $publisher->setEndpoints($params['publish endpoints']['sword']);
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
        $package = $this->crateManager->packageCrate($crateName);
        $data = array();
        try {
            $response = $this->publisher->publishCrate($package, $endpoint, $collection);
            $status = $response->sac_status;
            if($status == 201) {
                $data['msg'] = "$crateName successfully published to $collection";
            } else {
                $data['msg'] = "Error: $response->sac_statusmessage ($status)";
            }
        } catch (\Exception $e) {
            $status = 500;
            $data['msg'] = 'Error: '.$e->getMessage();
        }
        return new JSONResponse($data, $status);
    }

}
