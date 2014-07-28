// <?php

// /**
//  * ownCloud - Cr8it App
//  *
//  * @author Lloyd Harischandra
//  * @copyright 2014 University of Western Sydney www.uws.edu.au
//  *
//  * This library is free software; you can redistribute it and/or
//  * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
//  * License as published by the Free Software Foundation; either
//  * version 3 of the License, or any later version.
//  *
//  * This library is distributed in the hope that it will be useful,
//  * but WITHOUT ANY WARRANTY; without even the implied warranty of
//  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
//  *
//  * You should have received a copy of the GNU Lesser General Public
//  * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
//  *
//  */

// OCP\User::checkLoggedIn();
// OCP\App::checkAppEnabled('crate_it');
// $user = OCP\User::getUser();

// $field = isset($_POST['field']) ? $_POST['field'] : '';
// $email = isset($_POST['email']) ? $_POST['email'] : '';
// $dir = isset($_GET['dir']) ? $_GET['dir'] : '';
// $file = isset($_GET['file']) ? $_GET['file'] : '';
// $crate_id = isset($_GET['crate_id']) ? $_GET['crate_id'] : '';
// $crate_name = isset($_GET['crate_name']) ? $_GET['crate_name'] : '';
// $crate_description = isset($_GET['crate_description']) ? $_GET['crate_description'] : '';
// $neworder = isset($_GET['neworder']) ? $_GET['neworder'] : array();
// $element_id = isset($_POST['elementid']) ? $_POST['elementid'] : '';
// $new_title = isset($_POST['new_title']) ? $_POST['new_title'] : '';
// $new_name = isset($_POST['new_name']) ? $_POST['new_name'] : '';
// $file_id = isset($_GET['file_id']) ? $_GET['file_id'] : '';
// $level = isset($_GET['level']) ? $_GET['level'] : '';
// $description = isset($_POST['crate_description']) ? $_POST['crate_description'] : '';
// $keywords = isset($_POST['keywords']) ? $_POST['keywords'] : '';
// $id = isset($_POST['id']) ? $_POST['id'] : '';
// $name = isset($_POST['name']) ? $_POST['name'] : '';
// $new_full_name = isset($_POST['new_full_name']) ? $_POST['new_full_name'] : '';
// $vfs = isset($_POST['vfs']) ? $_POST['vfs'] : '';
// $sword_collection = isset($_POST['sword_collection']) ? $_POST['sword_collection'] : '';
// // $activity_id = isset($_POST['activity_id']) ? $_POST['activity_id'] : '';
// $grant_number = isset($_POST['grant_number']) ? $_POST['grant_number'] : '';
// $date = isset($_POST['date']) ? $_POST['date'] : '';
// $title = isset($_POST['title']) ? $_POST['title'] : '';
// // $keyword_activity = isset($_POST['keyword_activity']) ? $_POST['keyword_activity'] : '';

// $action = '';
// if (isset($_GET['action'])) {
// 	$action = $_GET['action'];
// } elseif (isset($_POST['action'])){
// 	$action = $_POST['action'];
// }

// $type = '';
// if (isset($_GET['type'])) {
// 	$type = $_GET['type'];
// } elseif (isset($_POST['type'])){
// 	$type = $_POST['type'];
// }

// //Get an instance of BagItManager
// $bagit_manager = \OCA\crate_it\lib\BagItManager::getInstance();

// $config = $bagit_manager->getConfig();

// switch ($action){
// 	case 'describe':
// 		$ok = $bagit_manager->setDescription($description);
// 		if($ok){
// 			echo json_encode(array("description" => $description));
// 		} else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
// 		break;
// 	case 'get_crate':
// 		$msg = $bagit_manager->getSelectedCrate();
// 		print $msg;
// 		break;
// 	case 'update_vfs':
//         $ok = $bagit_manager->updateVFS($vfs);
//         if($ok){
// 			echo $ok;
// 		}	else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
//         break;
// 	case 'rename_crate':
// 		// check if crate already exist
// 		$crate_list = $bagit_manager->getCrateList();
// 		$crate_already_exist = array_search($new_name, $crate_list);
// 		if ($crate_already_exist or $new_name==='default_crate') {
// 			header('HTTP/1.1 401 Crate with name "'.$new_name.'" already exists', 401);
// 		} elseif (empty($new_name)) {
// 			header('HTTP/1.1 401 Crate name cannot be blank', 401);
// 		} else {
// 			$ok = $bagit_manager->renameCrate($new_name, $vfs);
// 			if($ok){
// 				echo json_encode($new_name);
// 			}	else {
// 				header('HTTP/1.1 500 Internal Server Error');
// 			}
// 		}
// 		break;
// 	case 'preview':
// 		$preview = $bagit_manager->getPathFromFileId($file_id);
// 		if($preview){
// 			//echo $preview;
// 			$l = OCP\Util::linkTo( "file_previewer", "docViewer.php" );
// 			$l .= "?fn=".$preview;
// 			header("Location: ".$l);
// 		}
// 		else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
// 		break;
// 	case 'epub':
// 		$epub = $bagit_manager->createEpub();
// 		if(!isset($epub))
// 		{
// 			echo "No epub";
// 			break;
// 		}
// 		if (headers_sent()) throw new Exception('Headers sent.');
// 		while (ob_get_level() && ob_end_clean());
// 		if (ob_get_level()) throw new Exception('Buffering is still active.');
		
// 		$epub_name = $bagit_manager->getSelectedCrate();
// 		header("Content-type:application/epub+zip");
// 		header("Content-Type: application/force-download");
// 		header("Content-Disposition: attachment;filename=".$epub_name.".epub");
// 		readfile($epub);
// 		break;
// 	case 'zip':
// 		$crate_size = $bagit_manager->getCrateSize();
// 		$crate_size = $crate_size / (1024 * 1024);
// 		$max_zip_mb = $config['max_zip_mb'];
// 		if ($crate_size > $max_zip_mb) {
// 			echo 'WARNING: Crate size exceeds zip file limit: '.$max_zip_mb;
// 			break;
// 		}
// 		$zip_file = $bagit_manager->createZip();	
// 		if(!isset($zip_file)) {
// 			echo "No files in the bag to download";
// 			break;
// 		}
// 		$path_parts = pathinfo($zip_file);
// 		$filename = $path_parts['basename'];
// 		//Download file
// 		if (headers_sent()) throw new Exception('Headers sent.');
// 		while (ob_get_level() && ob_end_clean());
// 		if (ob_get_level()) throw new Exception('Buffering is still active.');
// 		header("Content-type:application/zip");
// 		header("Content-Type: application/force-download");
// 		header("Content-Disposition: attachment;filename=".$filename);
// 		readfile($zip_file);
// 		break;
// 	case 'postzip':
// 		$crate_size = $bagit_manager->getCrateSize();
// 		$crate_size = $crate_size / (1024 * 1024);
// 		$max_sword_mb = $config['max_sword_mb'];
// 		if ($crate_size > $max_sword_mb) {
// 			echo 'WARNING: Crate size exceeds SWORD limit: '.$max_sword_mb;
// 			break;
// 		}
// 		$zip_file = $bagit_manager->createZip();
// 		if(!isset($zip_file)) {
// 			echo "No files in the bag to download";
// 			break;
// 		}
// 		$path_parts = pathinfo($zip_file);
// 		$filename = $path_parts['basename'];

// 		// Post zip file to SWORD server
// 		// SWORD APP client instance
// 		require("swordappv2-php-library/swordappclient.php");
// 		$sac = new SWORDAPPClient();

// 		$sword_config = $config['sword'];
// 	   	$sd_uri = $sword_config['sd_uri'];
// 		$sword_username = $sword_config['username'];
// 		$sword_password = $sword_config['password'];
// 		$sword_obo = $sword_config['obo'];

// 		// Deposit
// 		$content_type = "application/zip";
// 		$packaging_format = "http://purl.org/net/sword/package/SimpleZip";
// 		$dr = $sac->deposit($sword_collection, $sword_username, $sword_password, $sword_obo, $zip_file, $packaging_format, $content_type, false);
// 		OCP\Util::writeLog("crate_it", $dr->sac_status." ".$dr->sac_statusmessage, OCP\Util::DEBUG);
// 		header("HTTP/1.1 ".$dr->sac_status." ".$dr->sac_statusmessage);
// 		break;
// 	case 'save_people':
// 		$success = $bagit_manager->savePeople($id, $name, $email);
// 		if($success){
// 			echo json_encode($name);
// 		}
// 		else {
// 			header('HTTP/1.1 400 people exists');
// 		}
// 		break;
// 	case 'remove_people':
// 		$success = $bagit_manager->removePeople($id);

// 		if($success){
// 			echo json_encode($id);
// 		}
// 		else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
// 		break;
// 	case 'edit_creator':
// 		$success = $bagit_manager->editCreator($creator_id, $new_full_name);

// 		if($success) {
// 			echo json_encode($new_full_name);
// 		}
// 		else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
// 		break;
// 	case 'crate_size':
// 		$size = $bagit_manager->getCrateSize();
// 		$data = array('size' => $size, 'human' => OCP\Util::humanFileSize($size));
// 		echo json_encode($data);
// 		break;
// 	case 'validate_metadata':
// 		$success = $bagit_manager->validateMetadata();

// 		if($success) {
// 			echo json_encode(array("status" => "Success"));
// 		}
// 		else {
// 			echo json_encode(array("status" => "Failed"));
// 		}
// 		break;
// 	case 'delete_crate':
// 		$result = $bagit_manager->deleteCrate();
// 		echo json_encode($result);
// 		break;
// 	case 'search':
// 		try {
// 			echo $bagit_manager->search($type, $keywords);
// 		} catch (Exception $e) {
// 			header('HTTP/1.1 500 '.$e->getMessage());
// 		}
// 		break;
// 	case 'get_for_codes':
// 		//need to access the tmpl var
// 		$results = $bagit_manager->lookUpMint("", 'top');
// 		foreach ($results as $item) {
// 			$vars = get_object_vars($item);
// 			if($vars["rdf:about"] === $level){
// 				//send skos:narrower array
// 				echo json_encode(array_values($vars['skos:narrower']));
// 			}
// 		}
// 		break;
// 	case 'save_activity':
// 		$success = $bagit_manager->saveActivity($id, $grant_number, $title, $date);

// 		if($success){
// 			echo json_encode($activity_id);
// 		}
// 		else {
// 			header('HTTP/1.1 400 grant number exists');
// 		}
// 		break;
// 	case 'remove_activity':
// 		$success = $bagit_manager->removeActivity($id);

// 		if($success){
// 			echo json_encode($activity_id);
// 		}
// 		else {
// 			header('HTTP/1.1 500 Internal Server Error');
// 		}
// 		break;
// 	case 'get_manifest':
// 		$success = $bagit_manager->getManifestData();
// 		if($success){
// 			echo json_encode($success);
// 		}	else {
// 			header('HTTP/1.1 500 Could not load manifest data');
// 		}
// 		break;
// 	case 'clear_field':
// 		// TODO: A lot of these actions could be removed if
// 	  // the payload would just specify the content it wanted to
// 	  // insert into the manifest.
// 		$success = $bagit_manager->clearMetadataField($field);
// 		if($success){
// 			echo json_encode($success);
// 		}	else {
// 			header('HTTP/1.1 500 Could not clear field');
// 		}
// 		break;
// }
