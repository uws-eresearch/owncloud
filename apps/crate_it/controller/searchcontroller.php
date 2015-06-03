<?php

namespace OCA\crate_it\Controller;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http;

require 'lib/mint_connector.php';
use \OCA\crate_it\lib\MintConnector;

require 'lib/curl_request.php';
use \OCA\crate_it\lib\CurlRequest;

class SearchController extends Controller {


    /**
     * @var $searchProvider
     */
    private $searchProvider;

    public function __construct($api, $request, $setupService) {
      parent::__construct($api, $request);
      $config = $setupService->getParams();
      $this->searchProvider = new MintConnector($config['mint']['url'], new CurlRequest());
    }


    /**
     * Search
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function search() {
        \OCP\Util::writeLog('crate_it', "SearchController::search()", \OCP\Util::DEBUG);
        $type = $this->params('type');
        $keywords = $this->params('keywords');
        try {
            $result = $this->searchProvider->search($type, $keywords);
            $response = new JSONResponse($result, 200);
        } catch (\Exception $e) {
            $response = new JSONResponse(array('msg' => $e->getMessage()), 500);
        }
        return $response;
    }


}