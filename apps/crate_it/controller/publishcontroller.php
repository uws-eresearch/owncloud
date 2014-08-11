<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class PublishController extends Controller {

    /**
     * @var $publisher
     */
    private $publisher;


    public function __construct($api, $request, $configManager) {
        parent::__construct($api, $request);
        $config = $configManager->readConfig();
        $config = $config['sword'];
        $this->$publisher = new SwordConnector($config['username'], $config['password'], $config['sd-uri'], $config['obo']);
    }


    /**
     * Get Collections
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getCollections() {
      \OCP\Util::writeLog('crate_it', "PublishController::getCollections()", \OCP\Util::DEBUG);
      return $this->publisher->getCollections();
    }

}