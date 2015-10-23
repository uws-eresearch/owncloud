<?php

namespace OCA\crate_it\Controller;

use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TextResponse;
use \OCP\AppFramework\Http;

use OCA\crate_it\lib\ZipDownloadResponse;
use OCA\crate_it\lib\XSendFileDownloadResponse;

class CrateController extends Controller {
    
    private $crateManager;
    
    public function __construct($appName, $request, $crateManager) {
        parent::__construct($appName, $request);
        $this->crateManager = $crateManager;
    }
    
    /**
     * Create crate with name and description
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function createCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::create()", \OCP\Util::DEBUG);
        $name = $this->params('name');
        $description = $this->params('description');
        $data_retention_period = 'Perpetuity';
        try {
            // TODO: maybe this selection stuff should be in a switchcrate method
            $msg = $this->crateManager->createCrate($name, $description, $data_retention_period);
            $_SESSION['selected_crate'] = $name;
            session_commit();
            return new JSONResponse(array('crateName' => $msg, 'crateDescription' => $description, 'crateDataRetentionPeriod' => $data_retention_period));
        } catch (\Exception $e) { // TODO: This is currently unreachable
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Get crate items
     * 
     * @Ajax
     * @NoAdminRequired
     */
    public function getManifest() // NOTE: this now return the entire manifest, should we change the name of the method?
    {
        \OCP\Util::writeLog('crate_it', "CrateController::get_manifest()", \OCP\Util::DEBUG);
        try {
            $crateName = $this->params('crate_id');
            $_SESSION['selected_crate'] = $crateName;
            session_commit();
            \OCP\Util::writeLog('crate_it', "selected_crate:: ".$_SESSION['selected_crate'], \OCP\Util::DEBUG);
            $data = $this->crateManager->getManifest($crateName);
            return new JSONResponse($data);
        } catch (\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
    
    
    /**
     * Add To Crate
     *
     * @Ajax
     * @NoAdminRequired
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
            $this->crateManager->addToCrate($crateName, $file);
            return new JSONResponse(array('msg' => "$file added to crate $crateName"));
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Crate Name
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function getCrateName()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateName()", \OCP\Util::DEBUG);

        $status = Http::STATUS_OK;
        if (array_key_exists('selected_crate',$_SESSION)) {
            $content = $_SESSION['selected_crate'];
        }else {
            $content = array('msg' => 'No selected crate.');
            $status = Http::STATUS_INTERNAL_SERVER_ERROR;
        }
        return new JSONResponse($content, $status);
    }
    
    /**
     * Get Crate Size
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function getCrateSize()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::getCrateSize()", \OCP\Util::DEBUG);
        try {
            $data = $this->crateManager->getCrateSize($_SESSION['selected_crate']);
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
     * @NoAdminRequired
     */
    public function updateCrate()
    {
        \OCP\Util::writeLog('crate_it', "CrateController::updateCrate()", \OCP\Util::DEBUG);
        $fieldsets = $this->params('fields');
        $savedFields = array();

        if (is_array($fieldsets)) {
            foreach ($fieldsets as $fieldset) {
                $field = $fieldset['field'];
                $value = $fieldset['value'];

                // TODO: This is an ugly workaround to avoid the max_input_vars ceiling
                // the vfs field is a json string inside a json object
                if ($field == 'vfs') {
                    $value = json_decode($value, true);
                }
                try {
                    $this->crateManager->updateCrate($_SESSION['selected_crate'], $field, $value);
                    $savedFields[$field] = $value;
                } catch (\Exception $e) {
                    return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
                }
            }
        }
        return new JSONResponse(array('msg' => "crate successfully updated", 'values' => $savedFields));
    }

    /**
     * Delete Crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function deleteCrate() {
        // TODO: all of these methods always return successfully, which shouldn't happen
        //       unfortunately this means rewriting methods in the bagit library
        \OCP\Util::writeLog('crate_it', "CrateController::deleteCrate()", \OCP\Util::DEBUG);
        $selected_crate = $_SESSION['selected_crate'];
        try {
            $this->crateManager->deleteCrate($selected_crate);
            return new JSONResponse(array('msg' => "Crate $selected_crate has been deleted"));
        } catch(\Exception $e) {
            return new JSONResponse(array('msg' => $e->getMessage()), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Rename Crate
     *
     * @Ajax
     * @NoAdminRequired
     */
    public function renameCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::renameCrate()", \OCP\Util::DEBUG);
        $oldCrateName = $_SESSION['selected_crate'];
        $newCrateName = $this->params('newCrateName');
        try {
            $this->crateManager->renameCrate($oldCrateName, $newCrateName);
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
     * @NoAdminRequired
     */
    public function packageCrate() {
        \OCP\Util::writeLog('crate_it', "CrateController::packageCrate()", \OCP\Util::DEBUG);
        try {
            $packagePath = $this->crateManager->packageCrate($_SESSION['selected_crate']);
            $filename = basename($packagePath);
            $response = new XSendFileDownloadResponse($packagePath, $filename);
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
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function generateEPUB() {
        \OCP\Util::writeLog('crate_it', "CrateController::generateEPUB()", \OCP\Util::DEBUG);
        try {
            $epubPath = $this->crateManager->generateEPUB($_SESSION['selected_crate']);
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
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function readmePreview() {
        \OCP\Util::writeLog('crate_it', "CrateController::readmePreview()", \OCP\Util::DEBUG);
        $readme = $this->crateManager->getReadme($_SESSION['selected_crate']);
        return new TextResponse($readme, 'html');
    }

    /**
     * README previewer - this is for debugging purposes.
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function xml() {
        \OCP\Util::writeLog('crate_it', "CrateController::readmePreview()", \OCP\Util::DEBUG);
        $readme = $this->crateManager->getReadme($_SESSION['selected_crate']);
        return new TextResponse($readme, 'html');
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
            $result = $this->crateManager->checkCrate($selected_crate);
            if (empty($result)) {
                $msg = 'All items are valid.';
            } else if (sizeof($result) === 1) {
                $msg = 'The following item no longer exists:';
            } else {
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
