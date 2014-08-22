<?php

namespace OCA\crate_it\Controller;

require 'apps/crate_it/lib/sword_connector.php';
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



    public function __construct($api, $request, $configManager, $crateManager) {
        parent::__construct($api, $request);
        $config = $configManager->readConfig();
        $this->crateManager = $crateManager;
        $sword = $config['sword'];
        $this->publisher = new SwordConnector($sword['username'], $sword['password'], $sword['sd_uri'], $sword['obo']);
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
        $collection = $this->params('collection');
        $package = $this->crateManager->packageCrate($crateName);
        $data = array();
        try {
            $response = $this->publisher->publishCrate($package, $collection);
            $status = $response->sac_status;
            if($status == 201) {
                $data['msg'] = "$status $crateName successfully published to $collection";
            } else {
                $data['msg'] = "Error: $status ".$reponse->sac_statusmessage;
            }
        } catch (Exception $e) {
            $status = 500;
            $data['msg'] = 'Error: '.$e->getMessage();
        }
        return new JSONResponse($data, $status);
    }

}
