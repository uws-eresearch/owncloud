<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

require 'apps/crate_it/lib/mint_connector.php';
use \OCA\crate_it\lib\MintConnector;

class SearchController extends Controller {


    /**
     * @var $searchProvider
     */
    private $searchProvider;

    public function __construct($api, $request, $configManager) {
      parent::__construct($api, $request);
      $config = $configManager->readConfig();
      $this->searchProvider = new MintConnector($config['mint']['url']);
    }


    /**
     * Search
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function search() {
        \OCP\Util::writeLog('crate_it', "SearchController::search()", \OCP\Util::DEBUG);
        $type = $this->params('type');
        $keywords = $this->params('keywords');
        try {
            $result = $this->searchProvider->search($type, $keywords);
            $response = new JSONResponse ($result, 200);
        } catch (Exception $e) {
            $reponse = new JSONResponse (array('statusText' => $e-getMessage()), 404);
        }
        return $response;
    }


}