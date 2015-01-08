<?php

namespace OCA\crate_it\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCA\crate_it\lib\SwordConnector;
use \OCP\AppFramework\Http\JSONResponse;

class PublishController extends Controller {

    private $publisher;
    private $crateManager;
    private $loggingService;
    private $shareService;
    private $mailer;

    public function __construct($appName, IRequest $request, $crateManager, $setupService, $publisher, $loggingService, $shareService, $mailer) {
        parent::__construct($appName, $request);
        $this->crateManager = $crateManager;
        $this->publisher = $publisher;
        $params = $setupService->getParams();
        $endpoints = $params['publish endpoints']['sword'];
        \OCP\Util::writeLog('crate_it', "PublishController::construct() - Publish enpoints: $endpoints", \OCP\Util::DEBUG);
        $publisher->setEndpoints($params['publish endpoints']['sword']);
        $this->loggingService = $loggingService;
        $this->shareService = $shareService;
        $this->mailer = $mailer;
        
        //Make sure publications folder exists
    	if (\OCP\USER::isLoggedIn()) {
    		$this->ensurePublishFolderExists();
    	}
        
    }
    
    private function ensurePublishFolderExists(){
    	\OCP\Util::writeLog('crate_it', "publishcontroller::ensurePublishFolderExists()", \OCP\Util::DEBUG);
    	$publicationsDir = $this->getPublicationsRoot();
    	
    	if (!file_exists($publicationsDir)) {
    		mkdir($publicationsDir, 0755, true);
    	}
    }
    
    private function getPublicationsRoot(){
    	$userId = \OCP\User::getUser();
    	$publicationsDir = \OC::$SERVERROOT.'/data/'.$userId.'/files/publications';
    	return $publicationsDir;
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
    public function publishCrate($name, $endpoint) {
        \OCP\Util::writeLog('crate_it', "PublishController::publishCrate()", \OCP\Util::DEBUG);
        
        $crateName = $this->params('name');
        $this->loggingService->log("Attempting to publish crate $name to publications folder");
        $this->loggingService->logManifest($name);
        
        $package = $this->crateManager->packageCrate($name);
        $zipname = basename($package, '.zip');
        $zipname .= '_' . date("Y-m-d_H:i:s") . '.zip';
        $this->loggingService->log("Zipped content into '$zipname'");
        $data = array();
        try {
            $this->loggingService->log("Publishing crate $name as ($zipname)..");
            
            if($endpoint === 'public'){
            	//write into public folder. 
            	$public_folder = $this->getPublicationsRoot() . '/public-open-access';
            	if(!file_exists($public_folder)){
            		mkdir($public_folder, 0755, true);
            	}
            	$dest = $public_folder . '/' . $zipname;
            	if(copy($package, $dest)){
            		
            		$fileInfo = \OC\Files\Filesystem::getFileInfo('publications/public-open-access/'.$zipname);
            		$fileId = $fileInfo->getId();
            		
            		//publish successful. Now share it
            		$token = $this->shareService->share('file', $fileId, \OCP\Share::SHARE_TYPE_LINK, null, \OCP\PERMISSION_READ, $zipname);
            		$link = \OCP\Util::linkToAbsolute('', 'index.php');
            		$link .= '/s/' . $token;
            		
            		$data['msg'] = "Crate '$name' successfully published to public folder\nThe link to the published crate is '$link'";
            		$this->loggingService->logPublishedDetails($dest, $name);
            	}
            	else{
            		$this->loggingService->log("Publishing crate '$name' failed.");
            		$data['msg'] = "Error: failed to copy $zipname ...";
            		$this->loggingService->log($data['msg']);
            	}
            } 
            else{
            	//Write into private folder
            	$private_folder = $this->getPublicationsRoot() . '/mediated-access';
            	if(!file_exists($private_folder)){
            		mkdir($private_folder, 0755, true);
            	}
            	$dest = $private_folder . '/' . $zipname;
            	if(copy($package, $dest)){
            		$data['msg'] = "Crate '$name' successfully published to private folder";
            		$this->loggingService->logPublishedDetails($dest, $name);
            	}
            	else{
            		$this->loggingService->log("Publishing crate '$name' failed.");
            		$data['msg'] = "Error: failed to copy $zipname ...";
            		$this->loggingService->log($data['msg']);
            	}
            }
            
        } catch (\Exception $e) {
            $this->loggingService->log("Publishing crate '$name' failed.");            
            $status = 500;
            $data['msg'] = "Error: failed to publish crate '$name' to publications folder: ".$e->getMessage();
            $this->loggingService->log($data['msg']);
        }
        $_SESSION['last_published_status'] = $data['msg'];

        return new JSONResponse($data, $status);
    }

}
