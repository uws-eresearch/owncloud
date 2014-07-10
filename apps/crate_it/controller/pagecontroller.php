<?php
namespace OCA\crate_it\Controller;

use \OCA\AppFramework\Controller\Controller;
use \OCA\AppFramework\Http\JSONResponse;
use \OCA\AppFramework\Http;

class PageController extends Controller {


	public function __construct($api, $request) {
		parent::__construct($api, $request);
	}

	/**
	 * Displays the index page of the ownCloud Invitations App
	 * IsAdminExemption is OK, because we want subadmins to access things
	 * CSRFExemption is OK for index
	 *
	 * @IsAdminExemption
	 * @CSRFExemption
	 */
	public function index() {
		//$uid = $this->api->getUserId();
		
		\OCP\User::checkLoggedIn();
		
		$user = \OCP\User::getUser();
		
		$bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();
		
		$manifestData = $bagit_manager->getManifestData();
		$config = $bagit_manager->getConfig();
		
		$description_length = empty($config['description_length']) ? 4000 : $config['description_length'];
		$max_sword_mb = empty($config['max_sword_mb']) ? 0 : $config['max_sword_mb'];
		$max_zip_mb = empty($config['max_zip_mb']) ? 0 : $config['max_zip_mb'];
		
		// create a new template to show the cart
		$tmpl = new \OCP\Template('crate_it', 'index', 'user');
		$tmpl->assign('previews', $bagit_manager->showPreviews());
		$tmpl->assign('bagged_files', $bagit_manager->getBaggedFiles());
		$tmpl->assign('description', $manifestData['description']);
		$tmpl->assign('description_length', $description_length);
		$tmpl->assign('crates', $bagit_manager->getCrateList());
		$tmpl->assign('selected_crate', $bagit_manager->getSelectedCrate());
		
		if ($manifestData['creators']) {
		   $tmpl->assign('creators', array_values($manifestData['creators']));
		}
		else {
		   $tmpl->assign('creators', array());
		}
		
		if ($manifestData['activities']) {
		   $tmpl->assign('activities', array_values($manifestData['activities']));
		}
		else {
		   $tmpl->assign('activities', array());
		}
		
		$tmpl->assign('mint_status', $bagit_manager->getMintStatus());
		$tmpl->assign('sword_status', $bagit_manager->getSwordStatus());
		$tmpl->assign('sword_collections', $bagit_manager->getCollectionsList());
		$tmpl->assign('max_sword_mb', $max_sword_mb);
		$tmpl->assign('max_zip_mb', $max_zip_mb);
		$tmpl->printPage();
		
		$model = array();
		return $this->render('index', $model);
	}

}