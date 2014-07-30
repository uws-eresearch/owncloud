<?php
/**
 * ownCloud - Cr8it App
 *
 * @author Lloyd Harischandra
 * @copyright 2014 University of Western Sydney www.uws.edu.au
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\crate_it\lib;
require 'mint_connector.php';

class BagItManager {
  var $base_dir;
  var $crate_dir;
  var $crate_root;
  var $manifest;
  var $selected_crate;
  var $bag;
  var $user;

  private static $instance = NULL;
  private static $searchProvider = NULL;

  /**
   * Set the values to following variables
   *
   * $fascinator - fascinator url
   * $base_dir - oc user's data directory
   * $crate_root - crates directory ( $base_dir/crates )
   * $crate_trash - crates trash directory ( $crate_root/.Trash )
   * $selected_crate - currently selected crate
   * $manifest - manifest which holds files info - id,title and absolute path
   */
  private function __construct() {
    $this->user = \OCP\User::getUser();
    self::$searchProvider = new MintConnector();
    $config_file = \OC::$SERVERROOT . '/data/cr8it_config.json';
    if (!file_exists($config_file)) {
      echo "No configuration file";
      return;
    }

    $this->base_dir = \OC::$SERVERROOT . '/data/' . $this->user;
    $this->crate_root = $this->base_dir . '/crates';
    $this->crate_trash = $this->crate_root . '/.Trash';
    if (!file_exists($this->crate_root)) {
      mkdir($this->crate_root);
    }

    if (!file_exists($this->crate_trash)) {
      mkdir($this->crate_trash);
    }

    // TODO: This msg will get printed 3 times with each page load, which shouldn't be happening
    //\OCP\Util::writeLog("crate_it", "creating new crate " . $_SESSION['crate_id'], 3);
    if (empty($_SESSION['crate_id'])) {
      $this->selected_crate = 'default_crate';
      $_SESSION['crate_id'] = 'default_crate';
      $this->createCrate('default_crate');
    } else {
      $this->initBag($_SESSION['crate_id']);
      $this->selected_crate = $_SESSION['crate_id'];
      $this->createManifest();
    }

    // $data_dir = $this->bag->getDataDirectory();
    // $this->manifest = $data_dir.'/manifest.json';

  }

  // Empty method to prevent copyting (Singleton pattern)
  private function __clone() {}

  // Singlton pattern
  // TODO: It looks like multiple instances are created with each page load
  // when there should only be one. Need to investigate
  public static function getInstance() {
    if(self::$instance == NULL) {
      self::$instance = new BagItManager();
      \OCP\Util::writeLog("crate_it", "BagItManager::getInstance()", \OCP\Util::DEBUG);
    }
    return self::$instance;
  }

  public function showPreviews() {
    $config = $this->getConfig();
    \OCP\Util::writeLog("crate_it", "BagItManager::showPreviews()", \OCP\Util::DEBUG);
    return $config['previews'];
  }

  public function createCrate($name) {
    \OCP\Util::writeLog("crate_it", "BagItManager::createCrate(".$name.")", \OCP\Util::DEBUG);
    if (empty($name)) {
      return false;
    }
    $this->selected_crate = $name;
    $this->initBag($name);
    $this->createManifest();
    return $name;
  }

  private function createManifest() {
    \OCP\Util::writeLog("crate_it", "BagItManager::createManifest()", \OCP\Util::DEBUG);
    if (!file_exists($this->manifest)) {
      $fp = fopen($this->manifest, 'x');
      $entry = array(
        'description' => '',
        'creators' => array() ,
        'activities' => array() ,
        'vfs' => array(
          array(
            'id' => 'rootfolder',
            'name' => $this->selected_crate,
            'folder' => true,
            'children' => array()
          )
        )
      );
      fwrite($fp, json_encode($entry));
      fclose($fp);
      $this->bag->update();
    }
  }

  public function switchCrate($name) {
    \OCP\Util::writeLog("crate_it", "BagItManager::switchCrate(".$name.")", \OCP\Util::DEBUG);
    if (empty($name)) {
      return false;
    }

    $this->initBag($name);
    $this->selected_crate = $name;
    $_SESSION['crate_id'] = $name;
    session_commit();
    return true;
  }

  private function initBag($name) {
    \OCP\Util::writeLog("crate_it", "BagItManager::initBag(".$name.")", \OCP\Util::DEBUG);
    $this->crate_dir = $this->crate_root . '/' . $name;
    $this->bag = new \BagIt($this->crate_dir);
    $data_dir = $this->bag->getDataDirectory();
    $this->manifest = $data_dir . '/manifest.json';
  }

  public function getSelectedCrate() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getSelectedCrate()", \OCP\Util::DEBUG);
    return $this->selected_crate;
  }

  public function getCrateList() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getCrateList()", \OCP\Util::DEBUG);
    $cratelist = array();
    if ($handle = opendir($this->crate_root)) {
      $filteredlist = array(
        '.',
        '..',
        'packages',
        '.Trash'
      );
      while (false !== ($file = readdir($handle))) {
        if (!in_array($file, $filteredlist)) {
          array_push($cratelist, $file);
        }
      }

      closedir($handle);
    }

    return $cratelist;
  }

  public function getBaggedFiles() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getBaggedFiles()", \OCP\Util::DEBUG);
    $contents = $this->getManifestData();
    return json_encode($contents['vfs']);
  }

  public function setBaggedFiles() {
  }

  public function addToBag($file) {
    \OCP\Util::writeLog("crate_it", "BagItManager::addToBag(".$file.")", \OCP\Util::DEBUG);
    $path_parts = pathinfo($file);
    $filename = $path_parts['filename'];
    if (\OC\Files\Filesystem::isReadable($file)) {

      // list($storage) = \OC\Files\Filesystem::resolvePath($file);
      // if ($storage instanceof \OC\Files\Storage\Local) {
      // 	$full_path = \OC\Files\Filesystem::getLocalFile($file);
      // 	if(!file_exists(\\OC::$SERVERROOT.'/data/fullpath.txt')){
      // 		$fp = fopen(\\OC::$SERVERROOT.'/data/fullpath.txt', 'w');
      // 		fwrite($fp, $full_path);
      // 		fclose($fp);
      // 	}
      // 	if($file === '/Shared' || is_dir($full_path))
      // 	{
      // 		return "Adding directories not supported yet";
      // 	}
      // }

    }
    elseif (!\OC\Files\Filesystem::file_exists($file)) {
      header("HTTP/1.0 404 Not Found");
      $tmpl = new OC_Template('', '404', 'guest');
      $tmpl->assign('file', $name);
      $tmpl->printPage();
    }
    else {
      header("HTTP/1.0 403 Forbidden");
      die('403 Forbidden');
    }

    $contents = json_decode(file_get_contents($this->manifest) , true); // convert it to an array.
    $vfs = & $contents['vfs'][0];
    if (array_key_exists('children', $vfs)) {
      $vfs = & $vfs['children'];
    }
    else {
      $vfs['children'] = array();
      $vfs = & $vfs['children'];
    }

    $this->addPath($file, $vfs);
    $fp = fopen($this->manifest, 'w');
    fwrite($fp, json_encode($contents));
    fclose($fp);

    // update the hashes

    $this->bag->update();
    return "File added to the crate " . $this->selected_crate;
  }

  private function getFullPath($file) {
    \OCP\Util::writeLog("crate_it", "BagItManager::getFullPath(".$file.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($file);
  }

  // TODO: There's currently no check for duplicates
  // TODO: root folder has isFolder set, so should other files folders

  private function addPath($path, &$vfs) {
    \OCP\Util::writeLog("crate_it", "BagItManager::addPath(".$path.")", \OCP\Util::DEBUG);
    if (\OC\Files\Filesystem::is_dir($path)) {
      $vfs_entry = array(
        'name' => basename($path) ,
        'id' => 'folder',
        'children' => array()
      );
      $vfs_contents = & $vfs_entry['children'];
      $paths = \OC\Files\Filesystem::getDirectoryContent($path);
      foreach($paths as $sub_path) {
        $rel_path = substr($sub_path['path'], strlen('files/'));
        if (!strncmp($path, "Shared", 6)) {
          $rel_path = 'Shared/' . $rel_path;
        }

        $this->addPath($rel_path, $vfs_contents);
      }
    }
    else {
      $full_path = $this->getFullPath($path);
      $id = md5($full_path);
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $full_path);
      finfo_close($finfo);
      $vfs_entry = array(
        'id' => $id,
        'name' => basename($path) ,
        'filename' => $full_path,
        'mime' => $mime
      );
    }

    array_push($vfs, $vfs_entry);
  }

  // TODO: Update this to fit with tree structure

  public function clearBag() {
    \OCP\Util::writeLog("crate_it", "BagItManager::clearBag()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $items = & $contents['titles'];
    $items = array();
    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    if (file_exists($this->crate_root . '/packages/crate.zip')) {
      unlink($this->crate_root . '/packages/crate.zip');
    }
  }

  public function renameCrate($new_name, $vfs) {
    \OCP\Util::writeLog("crate_it", "BagItManager::renameCrate(".$new_name.")", \OCP\Util::DEBUG);
    $this->updateVFS($vfs);
    rename($this->crate_dir, $this->crate_root . '/' . $new_name);
    $this->switchCrate($new_name);
    return true;
  }

  public function setDescription($description) {
    \OCP\Util::writeLog("crate_it", "BagItManager::setDescription()", \OCP\Util::DEBUG);
    $config = $this->getConfig();
    $max = $config['description_length'];
    if (strlen($description) > $max) {
      $description = substr($description, 0, $max);
    }

    $contents = json_decode(file_get_contents($this->manifest) , true);
    $contents['description'] = $description;
    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  /**
   * Get file path from file id
   *
   * @param string $file_id
   * @return string
   */
  public function getPathFromFileId($file_id) {
    \OCP\Util::writeLog("crate_it", "BagItManager::getPathFromFileId(".$file_id.")", \OCP\Util::DEBUG);
    foreach($this->flatList() as $value) {
      if ($value['id'] === $file_id) {
        $dir = $this->getParentDirectory($value['filename']);
        return $dir . '/' . $path_parts['basename'];
      }
    }
  }

  /**
   * Get the directory where a file resides.
   *
   * $file The file path
   */
  private function getParentDirectory($filePath) {
    \OCP\Util::writeLog("crate_it", "BagItManager::getParentDirectory()", \OCP\Util::DEBUG);
    $path_parts = pathinfo($filePath);
    $dir_prefix = \OC::$SERVERROOT . '/data/';
    $dir = str_replace($dir_prefix, "", $path_parts['dirname']);
    $dir_parts = explode("/", $dir);
    if ($this->user === $dir_parts[0]) {
      $dir = str_replace($this->base_dir . '/files', "", $path_parts['dirname']);
    }
    else {
      $dir = '/Shared' . str_replace($dir_prefix . $dir_parts[0] . '/files', "", $path_parts['dirname']);
    }

    return $dir;
  }

  private function getPreviewPath($full_path) {
    \OCP\Util::writeLog("crate_it", "BagItManager::getPreviewPath()", \OCP\Util::DEBUG);
    $path_parts = pathinfo($full_path);
    $prev_file = $path_parts['filename'] . '.htm';
    $temp_dir = OC_Helper::tmpFolder();
    $storage_id = \OCA\file_previewer\lib\Solr::getStorageId('full_path:"' . md5($full_path) . '"');
    $url = $this->fascinator['downloadURL'] . $storage_id . '/' . $prev_file;
    $url = str_replace(' ', '%20', $url);

    // Download file

    $comm = "wget -p --convert-links -nH -P " . $temp_dir . "previews " . $url;
    system($comm, $retval);
    if ($retval === 0) {
      $prev_path = $temp_dir . 'previews/portal/default/download/' . $storage_id;

      // make links to those htmls in temp dir

      return $prev_path . "/" . $prev_file;
    }
    else {
      return Null;
    }
  }

  public function createEpub() {
    \OCP\Util::writeLog("crate_it", "BagItManager::createEpub()", \OCP\Util::DEBUG);
    // create temp html from manifest

    $pre_content = "<html><body><h1>Table of Contents</h1><p style='text-indent:0pt'>";
    $source_dir = $this->base_dir . '/files';
    $temp_dir = OC_Helper::tmpFolder();
    foreach($this->flatList() as $value) {
      $path_parts = pathinfo($value['filename']);
      $preview_file = $path_parts['dirname'] . '/_html/' . $path_parts['basename'] . '/index.html';

      // skip files which don't have previews

      if (!file_exists($preview_file)) {
        continue;
      }

      $pre_content.= "<a href='file://" . $preview_file . "'>" . $value['name'] . "</a><br />";
    }

    $epub_title = $temp_dir . $this->selected_crate . '.html';
    $manifest_html = $pre_content . "</p></body></html>";
    if (is_dir($temp_dir)) {
      $fp = fopen($epub_title, 'w+');
      fwrite($fp, $manifest_html);
      fclose($fp);

      // feed it to calibre

      $escaped_title = str_replace(' ', '\ ', $epub_title);
      $command = 'ebook-convert ' . $escaped_title . ' ' . $temp_dir . 'temp.epub --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3';
      system($command, $retval);
    }

    // send the epub to user

    return $temp_dir . 'temp.epub';
  }

  public function flatList() {
    \OCP\Util::writeLog("crate_it", "BagItManager::flatList()", \OCP\Util::DEBUG);
    $data = $this->getManifestData();
    $vfs = &$data['vfs'][0]['children'];
    $flat = array();
    $ref = &$flat;
    $this->flat_r($vfs, $ref, $data['vfs'][0]['name']);
    return $flat;
  }

  private function flat_r(&$vfs, &$flat, $path) {
    if (count($vfs) > 0) {
      foreach($vfs as $entry) {
        if (array_key_exists('filename', $entry)) {
          $flat_entry = array(
            'id' => $entry['id'],
            'path' => $path,
            'name' => $entry['name'],
            'filename' => $entry['filename']
          );
          array_push($flat, $flat_entry);
        }
        elseif (array_key_exists('children', $entry)) {
          $this->flat_r($entry['children'], $flat, $path . $entry['name'] . '/');
        }
      }
    }
  }

  public function createZip() {
    \OCP\Util::writeLog("crate_it", "BagItManager::createZip()", \OCP\Util::DEBUG);
    $tmp_dir = OC_Helper::tmpFolder();
    OC_Helper::copyr($this->crate_dir, $tmp_dir);
    $bag = new \BagIt($tmp_dir);
    $manifest_data = $this->getManifestData();
    $creator_list = "";
    if ($manifest_data['creators']) {
      foreach($manifest_data['creators'] as $creator) {
        $creator_list = $creator_list . $creator['name'] . '<br/>';
      }
    }

    \OCP\Util::writeLog("crate_it", $creator_list, \OCP\Util::DEBUG);
    // TODO: Refactor this into a separate template file
    $metadata = '<html><head><title>' . $this->selected_crate . '</title></head><body><article>
					<h1><u>"' . $this->selected_crate . '" Data Package README file</u></h1>
					<section resource="creative work" typeof="http://schema.org/CreativeWork">
							  <h1>Package Title</h1>
							  <span property="http://schema.org/name http://purl.org/dc/elements/1.1/title">' . $this->selected_crate . '</span>
							  <h1>Package Creation Date</h1>
							  <span content="' . date("Y-m-d H:i:s") . '" property="http://schema.org/dateCreated">' . date("F jS, Y") . '</span>
							  <h1>Package File Name</h1>
							  <span property="http://schema.org/name">' . $this->selected_crate . '.zip</span>
							  <h1>ID</h1>
							  <span property="http://schema.org/id">' . $this->selected_crate . '</span>
							  <h1>Description</h1>
							  <span property="http://schema.org/description">' . $manifest_data['description'] . '</span>
							  <h1>Creators</h1>
							  <span property="http://schema.org/creators">' . $creator_list . '</span>
							  <h1>Software Information</h1>
							  <section property="http://purl.org/dc/terms/creator" typeof="http://schema.org/softwareApplication" resource="">
							  	<table>
							  		<tbody>
							  			<tr>
							  				<td>Generating Software Application</td>
							  				<td property="http://schema.org/name">Cr8it</td>
							  			</tr>
							  			<tr>
							  				<td>Software Version</td>
							  				<td property="http://schema.org/softwareVersion">v0.1</td>
							  			</tr>
							  			<tr>
							  				<td>URLs</td>
							  				<td>
							  					<span><a href="https://github.com/uws-eresearch/apps" property="http://schema.org/url">
							  								https://github.com/uws-eresearch/apps</a></span>
							  					<span><a href="http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository/" property="http://schema.org/url">
							  								http://eresearch.uws.edu.au/blog/projects/projectsresearch-data-repository</a></span>
							  				</td>
							  			</tr>
							  		</tbody>
							  	</table>
							  </section>
						   </section>
						   <h1>Organisational Information</h1>
						   <section property="http://purl.org/dc/terms/references" typeof="http://schema.org/Organisation" resource="">
							  	<table>
							  		<tbody>
							  			<tr>
							  				<td></td>
							  				<td></td>
							  			</tr>
							  		</tbody>
							  	</table>
						   </section>
						   <h1>Summary of Files</h1>
							 <table>
							  		<thead>
							  			<tr>
							  				<th>Name</th>
							  				<th>Folder</th>
							  				<th>Title</th>
							  				<th>Type</th>
							  				<th>Size</th>
							  				<th>Research</th>
							  				<th>Download</th>
							  				<th>View</th>
							  		    <tr>
							  		</thead>
							  		<tbody>';
    if (count($bag->getBagErrors(true)) == 0) {
      foreach($this->flatList() as $item) {
        $path_parts = pathinfo($item['filename']);
        $dir = $this->getParentDirectory($item['filename']);
        $bag->addFile($item['filename'], $dir . '/' . $path_parts['basename']);
        $name = empty($dir) ? $path_parts['basename'] : $dir . '/' . $path_parts['basename'];

        // Please note that this doesn't work in windows environments

        $file = escapeshellarg($item['filename']);
        $mime = shell_exec("file -bi " . $file);
        $mime = substr($mime, 0, strpos($mime, ';'));
        $size = $this->humanReadableFileSize(filesize($item['filename']));
        $sec = '<tr>
							<td>' . $name . '</td>
							<td>' . $item['path'] . '</td>
							<td>' . $item['name'] . '</td>
							<td>' . $mime . '</td>
							<td>' . $size . '</td>
							<td></td>
							<td><a href="">Download</a></td>
							<td><a href="">View</a></td>
						</tr>';
        $metadata.= $sec;
      }

      $metadata.= '</article></body></html>';

      // now add the readme file

      $readme = $tmp_dir . 'data/README.html';
      $fp = fopen($readme, 'w+');
      fwrite($fp, $metadata);
      fclose($fp);
      $bag->update();
      $bag->package($tmp_dir . '/' . $this->selected_crate, 'zip');
      return $tmp_dir . '/' . $this->selected_crate . '.zip';
    }
    else {
      $errors = $bag->getBagErrors(true);
      print var_dump($errors);
    }
  }

  private function humanReadableFileSize($bytes, $decimals = 2) {
    \OCP\Util::writeLog("crate_it", "BagItManager::humanReadableFileSize()", \OCP\Util::DEBUG);
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
  }

  public function search($type, $keywords) {
    \OCP\Util::writeLog("crate_it", "BagItManager::search()", \OCP\Util::DEBUG);
    return self::$searchProvider->search($type, $keywords); 
  }

  public function getManifestData() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getManifestData()", \OCP\Util::DEBUG);
    // read from manifest
    $fp = fopen($this->manifest, 'r');
    $contents = file_get_contents($this->manifest);
    $cont_array = json_decode($contents, true);
    fclose($fp);
    return $cont_array;
  }



  public function savePeople($id, $name, $email) {
    \OCP\Util::writeLog("crate_it", "BagItManager::savePeople()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $new_creator = array('id' => $id, 'name' => $name, 'email' => $email);
    if ($contents['creators']) {
      $creators = &$contents['creators'];
      for ($i = 0; $i < count($creators); $i++) {
        if ($creators[$i]['id'] == $id) {
          return false;
        }
      }
      array_push($creators, $new_creator);
    } else {
      $contents['creators'] = array($new_creator);
    }

    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function removePeople($id) {
    \OCP\Util::writeLog("crate_it", "BagItManager::removePeople()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $creators = & $contents['creators'];
    for ($i = 0; $i < count($creators); $i++) {
      if ($creators[$i]['id'] == $id) {
        array_splice($creators, $i, 1);
      }
    }

    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function clearMetadataField($field) {
    \OCP\Util::writeLog("crate_it", "BagItManager::clearMetadataField()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $contents[$field] = array();
    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function editCreator($id, $new_name) {
    \OCP\Util::writeLog("crate_it", "BagItManager::editCreator()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $creators = & $contents['creators'];
    for ($i = 0; $i < count($creators); $i++) {
      if ($creators[$i]['id'] == $id) {
        $creators[$i]['name'] = $new_name;
      }
    }

    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function updateVFS($data) {
    \OCP\Util::writeLog("crate_it", "BagItManager::updateVFS()", \OCP\Util::DEBUG);
    $new_vfs = json_decode($data);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $fp = fopen($this->manifest, 'w+');
    $contents['vfs'] = $new_vfs;
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function validateMetadata() {
    \OCP\Util::writeLog("crate_it", "BagItManager::validateMetadata()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    if (count($contents['creators']) > 0 && $contents['description'] && trim($contents['description']) != '') {
      return true;
    }
    else {
      return false;
    }
  }

  function getCollectionsList() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getCollectionsList()", \OCP\Util::DEBUG);
    require ("swordappv2-php-library/swordappclient.php");

    $sac = new \SWORDAPPClient();
    $config = $this->getConfig();

    // FIXME: make these configurable

    /*$sd_uri = "http://115.146.93.246/sd-uri";
    $sword_username = "uws_sword";
    $sword_password = "swordAdmin";
    $sword_obo = "obo";*/
    $sd_uri = $config["sword"]["sd_uri"];
    $sword_username = $config["sword"]["username"];
    $sword_password = $config["sword"]["password"];
    $sword_obo = $config["sword"]["obo"];

    // Get service document

    $sd = $sac->servicedocument($sd_uri, $sword_username, $sword_password, $sword_obo);
    $collections = array();
    if ($sd->sac_status == 200) {
      foreach($sd->sac_workspaces as $workspace) {
        foreach($workspace->sac_collections as $collection) {
          $collections["$workspace->sac_workspacetitle - $collection->sac_colltitle"] = $collection->sac_href;
        }
      }
    }
    else {
      header("HTTP/1.1 " . $sd->sac_status . " " . $sd->sac_statusmessage);

      // break;

    }

    return $collections;
  }

  public static function getConfig() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getConfig()", \OCP\Util::DEBUG);
    $config = null;
    $config_file = \OC::$SERVERROOT . '/data/cr8it_config.json';
    if (file_exists($config_file)) {
      $config = json_decode(file_get_contents($config_file) , true); // convert it to an array.
    }

    return $config;
  }

  public function getCrateSize() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getCrateSize()", \OCP\Util::DEBUG);
    $files = $this->flatList();
    $total = 0;
    foreach($files as $file) {
      $total+= filesize($file['filename']);
    }

    return $total;
  }

  public function getMintStatus() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getMintStatus()", \OCP\Util::DEBUG);
    $config = $this->getConfig();
    if ($config['mint']) {
      // \OCP\Util::writeLog("crate_it", $config['mint']['status'], \OCP\Util::DEBUG);
      return $config['mint']['status'];
    }
    else {
      return false;
    }
  }

  public function getSwordStatus() {
    \OCP\Util::writeLog("crate_it", "BagItManager::getSwordStatus()", \OCP\Util::DEBUG);
    $config = $this->getConfig();
    if ($config['sword']) {
      // \OCP\Util::writeLog("crate_it", $config['sword']['status'], \OCP\Util::DEBUG);
      return $config['sword']['status'];
    }
    else {
      return false;
    }
  }

  public function deleteCrate() {
    \OCP\Util::writeLog("crate_it", "BagItManager::deleteCrate()", \OCP\Util::DEBUG);
    // Implement a simple trash bin

    $crate_name = basename($this->crate_dir);
    $trash_dir = $this->crate_trash . "/" . $crate_name . "_" . date(DATE_ISO8601);
    // \OCP\Util::writeLog("crate_it", $trash_dir, \OCP\Util::DEBUG);
    try {
      rename($this->crate_dir, $trash_dir);
      $_SESSION['crate_id'] = '';
      session_commit();
      return array(
        "status" => "Success"
      );
    }

    catch(Exception $e) {
      return array(
        "status" => "Failed",
        "msg" => $e->getMessage()
      );
    }
  }

  public function saveActivity($id, $grant_number, $title, $date) {
    \OCP\Util::writeLog("crate_it", "BagItManager::saveActivity()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $new_activity = array('id' => $id, 'grant_number' => $grant_number, 'title' => $title, 'date' => $date);
    if ($contents['activities']) {
      $activities = & $contents['activities'];
      for ($i = 0; $i < count($activities); $i++) {
        if ($activities[$i]['id'] == $id) {
          return false;
        }
      }
      array_push($activities, $new_activity);
    } else {
      $contents['activities'] = array($new_activity);
    }
    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }

  public function removeActivity($id) {
    \OCP\Util::writeLog("crate_it", "BagItManager::removeActivity()", \OCP\Util::DEBUG);
    $contents = json_decode(file_get_contents($this->manifest) , true);
    $activities = & $contents['activities'];
    for ($i = 0; $i < count($activities); $i++) {
      if ($activities[$i]['id'] == $id) {
        array_splice($activities, $i, 1);
      }
    }

    $fp = fopen($this->manifest, 'w+');
    fwrite($fp, json_encode($contents));
    fclose($fp);
    $this->bag->update();
    return true;
  }
}
