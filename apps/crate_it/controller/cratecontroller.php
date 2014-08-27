<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\TextResponse;
use \OCA\AppFramework\Http;

require 'apps/crate_it/lib/zipdownloadresponse.php';
use OCA\crate_it\lib\ZipDownloadResponse;

class CrateController extends Controller {
    
    /**
     * @var $twig
     */
    private $twig;
    
    /**
     * @var $crate_service
     */
    private $crate_service;
    
    public function __construct($api, $request, $crate_service, $setupService) {
        parent::__construct($api, $request);
        $setupService->getParams();
        $this->crate_service = $crate_service;
    }
    
    /**
     * Create crate with name and description
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function createCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::create()", \OCP\Util::DEBUG);
        $name = $this->params('name');
        $description = $this->params('description');
        try {
            // TODO: maybe this selection stuff should be in a switchcrate method
            $msg = $this->crate_service->createCrate($name, $description);
            $_SESSION['selected_crate'] = $name;
            session_commit();
            return new JSONResponse(array('crateName' => $msg, 'crateDescription' => $description), 200);
        } catch (Exception $e) {
            return new JSONResponse (
                array ($e->getMessage(), 'error' => $e),
                $e->getCode()
            );
        }
    }
    
    /**
     * Get crate items
     * 
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getItems()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::get_items()", \OCP\Util::DEBUG);
        try {
            $crateName = $this->params('crate_id');
            $_SESSION['selected_crate'] = $crateName;
            session_commit();
            \OCP\Util::writeLog('crate_it', "selected_crate:: ".$_SESSION['selected_crate'], \OCP\Util::DEBUG);
            $data = $this->crate_service->getItems($crateName);
            return new JSONResponse($data, 200);
        } catch (Exception $e) {
            return new JSONResponse(
                array('msg' => "Error getting manifest data", 'error' => $e),
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
        \OCP\Util::writeLog('crate_it', "CrateController::add()", \OCP\Util::DEBUG);
        try
        {
            // TODO check if this error handling works
            $file = $this->params('file');
            \OCP\Util::writeLog('crate_it', "Adding ".$file, 3);
            $msg = $this->crate_service->addToBag($_SESSION['selected_crate'], $file);
            return new JSONResponse ($msg, 200);
        } catch(Exception $e)
        {
            return new JSONResponse(
                array('msg' => "Error adding file", 'error' => $e),
                $e->getCode()
            );
        }
       
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
        \OCP\Util::writeLog('crate_it', "CrateController::manifest()", \OCP\Util::DEBUG);
        $success = $this->crate_service->getManifest();
        return new JSONResponse (array('msg'=>'OK'), $success);
    }
    
    /**
     * Get Crate Size
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getCrateSize()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateSize()", \OCP\Util::DEBUG);
        $data = $this->crate_service->getCrateSize($_SESSION['selected_crate']);
        return new JSONResponse($data, 200);
    }
    
    /**
     * Update Crate
     * TODO change to not just return description but all fields?
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function updateCrate()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::updateCrate()", \OCP\Util::DEBUG);
        $field = $this->params('field');
        $value = $this->params('value');
        $this->crate_service->updateCrate($_SESSION['selected_crate'], $field, $value);
        return new JSONResponse(array('description' => $value), 200);
    }

    /**
     * Delete Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function deleteCrate() {
        // TODO: all of these methods always return successfully, which shouldn't happen
        //       perhaps messages and response codes should be created by the CrateService?
        \OCP\Util::writeLog('crate_it', "CrateController::deleteCrate()", \OCP\Util::DEBUG);
        $selected_crate = $_SESSION['selected_crate'];
        $this->crate_service->deleteCrate($selected_crate);
        // TODO: No $data?
        $msg = 'Crate '.$selected_crate.' is deleted';
        return new JSONResponse($msg, 200);
    }

    /**
     * Rename Crate
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function renameCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::renameCrate()", \OCP\Util::DEBUG);
        $oldCrateName = $_SESSION['selected_crate'];
        $newCrateName = $this->params('newCrateName');
        $this->crate_service->renameCrate($oldCrateName, $newCrateName);
        // TODO: need method for setting selected crate
        $_SESSION['selected_crate'] = $newCrateName;
        session_commit();
        // TODO: No $data?
        return new JSONResponse($data, 200);
    }
    
    /**
     * Package Crate as a Zip
     *
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function packageCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::packageCrate()", \OCP\Util::DEBUG);
        $packagePath = $this->crate_service->packageCrate($_SESSION['selected_crate']);
        $filename = basename($packagePath);
        $response = new ZipDownloadResponse($packagePath, $filename);
        return $response;
    }
    

    /**
     * README previewer - this is for debugging purposes.
     *
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function readmePreview() {
        \OCP\Util::writeLog('crate_it', "CrateController::readmePreview()", \OCP\Util::DEBUG);
        $readme = $this->crate_service->getReadme($_SESSION['selected_crate']);
        return new TextResponse($readme, 'html');
    }


    /**
     * Check crate 
     *
     * @Ajax
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function checkCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::checkCrate()", \OCP\Util::DEBUG);
        try {
            $selected_crate = $_SESSION['selected_crate'];
            $result = $this->crate_service->checkCrate($selected_crate);
            if (empty($result)) {
                $msg = 'All items are valid.';
            }
            else if (sizeof($result) === 1) {
                $msg = 'The following item no longer exists:';
            }
            else {
                $msg = 'The following items no longer exist:';
            }
            return new JSONResponse(
                array('msg' => $msg, 
                      'result' => $result), 
                200
            );
        } catch (Exception $e) {
            return new JSONResponse (
                array ($e->getMessage(), 'error' => $e),
                $e->getCode()
            );
        }
    }
}
