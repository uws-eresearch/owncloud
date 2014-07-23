<?php

namespace OCA\crate_it\lib;

require 'apps/crate_it/3rdparty/BagIt/bagit.php';
use BagIt;

class Crate extends BagIt {

  private $manifestPath;
  private $name;

  public function __construct($crateName, $description='') {
      parent::__construct($crateName);
      $this->manifestPath = $this->getDataDirectory().'/manifest.json';
      if (!file_exists($this->manifestPath)) {
        $this->createManifest($description);
      }
      $tmp = explode('/', $crateName);
      $this->name = end($tmp);
  }

  private function createManifest($description) {
    \OCP\Util::writeLog('crate_it', "Crate::createCrate(".$this->bag.','.$description.")", \OCP\Util::DEBUG);
    $description = NULL ? '' : $description;
    $entry = array(
      'description' => $description,
      'creators' => array() ,
      'activities' => array() ,
      'vfs' => array(
        array(
          'id' => 'rootfolder',
          'name' => $this->name, // TODO: This is working for some reason
          'folder' => true,
          'children' => array()
        )
      )
    );
    $this->writeFile($this->manifestPath, json_encode($entry));
    $this->update();
  }


  public function getManifest() {
    $manifest = $this->readFile($this->manifestPath);
    return json_decode($manifest, true);
  }

  public function setManifest($manifest) {
    $manifest = json_encode($manifest);
    $this->writeFile($this->manifestPath, $manifest);
  }

  public function addToCrate($path) {
    \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$path.")", \OCP\Util::DEBUG);
    $manifest = $this->getManifest();
    // $contents = json_decode(file_get_contents($manifest_path) , true); // convert it to an array.
    $vfs = &$manifest['vfs'][0];
    // TODO: we should be able to get rid of this if we initialise the manifest correctly
    if (array_key_exists('children', $vfs)) {
      $vfs = &$vfs['children'];
    } else {
      $vfs['children'] = array();
      $vfs = &$vfs['children'];
    }
    $this->addPath($path, $vfs);
    $this->setManifest($manifest);
    $this->update();
  }

  // TODO: There's currently no check for duplicates
  // TODO: root folder has isFolder set, so should other files folders
  // TODO: refactor to two helpers, addFolder and addFile
  private function addPath($path, &$vfs) {
    \OCP\Util::writeLog('crate_it', "CrateManager::addPath(".$path.")", \OCP\Util::DEBUG);
    if (\OC\Files\Filesystem::is_dir($path)) {
      $vfsEntry = $this->addFolderToCrate($path);
    } else {
      $vfsEntry = $this->addFileToCrate($path);
    }
    array_push($vfs, $vfsEntry);
  }

  private function addFolderToCrate($folder) {
    $vfsEntry = array(
      'name' => basename($folder) ,
      'id' => 'folder',
      'children' => array()
    );
    $vfsContents = &$vfsEntry['children'];
    $paths = \OC\Files\Filesystem::getDirectoryContent($folder);
    foreach($paths as $path) {
        $relativePath = substr($path['path'], strlen('files/'));
        if (!strncmp($path, "Shared", 6)) {
            $relativePath = 'Shared/'.$relativePath;
        }
        $this->addPath($relativePath, $vfsContents);
    }
  }


  private function addFileToCrate($file) {
    $fullPath = $this->getFullPath($file);
    $id = md5($fullPath);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fullPath);
    finfo_close($finfo);
    $vfsEntry = array(
      'id' => $id,
      'name' => basename($file) ,
      'filename' => $fullPath,
      'mime' => $mime
    );
    return $vfsEntry;
  }


  // TODO: Move to utility class
  private function writeFile($path, $contents) {
    \OCP\Util::writeLog('crate_it', "Crate::writeToFile(".$path.")", \OCP\Util::DEBUG);
    $fp = fopen($path, 'w');
    fwrite($fp, $contents);
    fclose($fp);
  }

  // TODO: Move to utility class
  private function readFile($path) {
    $fp = fopen($path, 'r');
    $contents = file_get_contents($path);
    fclose($fp);
    return $contents;
  }

  private function getFullPath($path) {
    \OCP\Util::writeLog('crate_it', "CrateManager::getFullPath(".$path.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($path);
  }

}