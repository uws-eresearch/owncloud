<?php

namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http\TextResponse;
use \OCP\AppFramework\Http;

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
    
    public function __construct($api, $request, $crate_service) {
        parent::__construct($api, $request);
        $this->crate_service = $crate_service;
    }
    
    /**
     * Create crate with name and description
     *
     * @Ajax
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
            return new JSONResponse(array('crateName' => $msg, 'crateDescription' => $description));
        } catch (\Exception $e) { // TODO: This is currently unreachable
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Get crate items
     * 
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getItems() // NOTE: this now return the entire manifest, should we change the name of the method?
    {
        \OCP\Util::writeLog('crate_it', "CrateController::get_items()", \OCP\Util::DEBUG);
        try {
            $crateName = $this->params('crate_id');
            $_SESSION['selected_crate'] = $crateName;
            session_commit();
            \OCP\Util::writeLog('crate_it', "selected_crate:: ".$_SESSION['selected_crate'], \OCP\Util::DEBUG);
            $data = $this->crate_service->getItems($crateName);
            return new JSONResponse($data);
        } catch (\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    /**
     * Add To Crate
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function add() {
        \OCP\Util::writeLog('crate_it', "CrateController::add()", \OCP\Util::DEBUG);
        try {
            // TODO check if this error handling works
            $file = $this->params('file');
            \OCP\Util::writeLog('crate_it', "Adding ".$file, \OCP\Util::DEBUG);
            if($file == '_html' && \OC\Files\Filesystem::is_dir($file)) {
                throw new \Exception("$file ignored by Crate it");
            }
            $crateName = $_SESSION['selected_crate'];
            // TODO: naming consistency, add vs addToBag vs addToCrate
            $this->crate_service->addToBag($crateName, $file);
            return new JSONResponse(array('msg' => "$file added to crate $crateName"));
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    
    /**
     * Get Crate Size
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function getCrateSize()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateSize()", \OCP\Util::DEBUG);
        try {
            $data = $this->crate_service->getCrateSize($_SESSION['selected_crate']);
            return new JSONResponse($data);
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Update Crate
     * TODO change to not just return description but all fields?
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function updateCrate()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::updateCrate()", \OCP\Util::DEBUG);
        $field = $this->params('field');
        $value = $this->params('value');
        try {
            $this->crate_service->updateCrate($_SESSION['selected_crate'], $field, $value);
            return new JSONResponse(array('msg' => "$field successfully updated", 'value' => $value));
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete Crate
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function deleteCrate() {
        // TODO: all of these methods always return successfully, which shouldn't happen
        //       unfortunately this means rewriting methods in the bagit library
        \OCP\Util::writeLog('crate_it', "CrateController::deleteCrate()", \OCP\Util::DEBUG);
        $selected_crate = $_SESSION['selected_crate'];
        try {
            $this->crate_service->deleteCrate($selected_crate);
            return new JSONResponse(array('msg' => "Crate $selected_crate has been deleted"));
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rename Crate
     *
     * @Ajax
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function renameCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::renameCrate()", \OCP\Util::DEBUG);
        $oldCrateName = $_SESSION['selected_crate'];
        $newCrateName = $this->params('newCrateName');
        try {
            $this->crate_service->renameCrate($oldCrateName, $newCrateName);
            $_SESSION['selected_crate'] = $newCrateName;
            session_commit();
            return new JSONResponse(array('msg' => "Renamed $oldCrateName to $newCrateName"));
        } catch (\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Package Crate as a Zip
     * 
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function packageCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::packageCrate()", \OCP\Util::DEBUG);
        try {
            $packagePath = $this->crate_service->packageCrate($_SESSION['selected_crate']);
            $filename = basename($packagePath);
            $response = new ZipDownloadResponse($packagePath, $filename);
        } catch(\Exception $e) {
            $message = 'Internal Server Error: '.$e->getMessage();
            \OCP\Util::writeLog('crate_it', $message, \OCP\Util::ERROR);
            $response = new TextResponse($message);
            $response->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
        }
        return $response;
    }
  

    /**
     * Create ePub
     *     
     * @CSRFExemption
     * @IsAdminExemption
     * @IsSubAdminExemption
     */
    public function generateEPUB() {
        \OCP\Util::writeLog('crate_it', "CrateController::generateEPUB()", \OCP\Util::DEBUG);
        try {
            $epubPath = $this->crate_service->generateEPUB($_SESSION['selected_crate']);
            $filename = basename($epubPath);
            $response = new ZipDownloadResponse($epubPath, $filename);
        } catch(\Exception $e) {
            $message = 'Internal Server Error: '.$e->getMessage();
            \OCP\Util::writeLog('crate_it', $message, \OCP\Util::ERROR);
            $response = new TextResponse($message);
            $response->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
        }
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
                      'result' => $result)
            );
        } catch (\Exception $e) {
            return new JSONResponse (array($e->getMessage(), 'error' => $e), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
