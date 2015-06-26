<?php

namespace OCA\crate_it\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;


// TODO: Remove this class and move checkCrate method to CrateController
class CrateCheckController extends Controller {

    private $crateManager;
    private $loggingService;

    public function __construct($appName, $request, $crateManager, $loggingService) {
        parent::__construct($appName, $request);
        $this->crateManager = $crateManager;
        $this->loggingService = $loggingService;
    }

    /**
     * Check crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function checkCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::checkCrate()", \OCP\Util::DEBUG);
        try {
            $selected_crate = $_SESSION['selected_crate'];
            $this->loggingService->log("Beginning Consistency Check for crate '$selected_crate'..");
            $result = $this->crateManager->checkCrate($selected_crate);
            if(empty($result)) {
                $msg = 'All items are valid.';
            } else {
                if(sizeof($result) === 1) {
                    $msg = 'The following item no longer exists:';
                } else {
                    $msg = 'The following items no longer exist:';
                }
            }
            $this->loggingService->log("Consistency Check Result - $msg");
            foreach($result as $key => $value) {
                $this->loggingService->log($key);
            }
            $this->loggingService->log("Finished Consistency Check.");

            return new JSONResponse(
                array('msg' => $msg,
                    'result' => $result),
                200
            );
        } catch(Exception $e) {
            $ecode = $e->getCode();
            $msg = $e->getMessage();

            $this->loggingService->log("Error ($ecode) during Consistency Check");
            $this->loggingService->log($msg);
            return new JSONResponse (
                array($msg, 'error' => $e),
                $ecode
            );
        }
    }
}

    