<?php

namespace OCA\crate_it\lib;

require '3rdparty/BagIt/bagit.php';
use BagIt;

class Crate extends BagIt {

  private $manifestPath;
  private $crateName;
  private $crateRoot;

  public function __construct($crateRoot, $crateName, $description='') {
      \OCP\Util::writeLog('crate_it', "Crate::__construct(".$crateRoot.','.$crateName.','.$description.")", \OCP\Util::DEBUG);
      parent::__construct($this->getAbsolutePath($crateRoot, $crateName));     
      $this->crateName = $crateName;
      $this->crateRoot = $crateRoot;
      $this->manifestPath = $this->getDataDirectory().'/manifest.json';
      if (!file_exists($this->manifestPath)) {
        $this->createManifest($description);
      }
  }

  private function createManifest($description) {
    \OCP\Util::writeLog('crate_it', "Crate::createManifest(".$description.")", \OCP\Util::DEBUG);
    $description = NULL ? '' : $description;
    $entry = array(
      'description' => $description,
      'creators' => array() ,
      'activities' => array() ,
      'vfs' => array(
        array(
          'id' => 'rootfolder',
          'name' => $this->crateName, 
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
    //\OCP\Util::writeLog('crate_it', "Manifest before decode: ".$manifest, \OCP\Util::DEBUG);    
    return json_decode($manifest, true);
  }

  public function setManifest($manifest) {
    $manifest = json_encode($manifest);
    $this->writeFile($this->manifestPath, $manifest);
  }

  public function addToCrate($path) {
    \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$path.")", \OCP\Util::DEBUG);
    $manifest = $this->getManifest();
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

  public function updateCrate($field, $value) {
     \OCP\Util::writeLog("crate_it", "Crate::updateCrate($field, $value)", \OCP\Util::DEBUG);
     $manifest = $this->getManifest();
     if($value === NULL) {
        if(is_array($manifest[$field])) {
          $value = array();
        } else {
          $value = '';
        }
     }
     $manifest[$field] = $value;
     $this->setManifest($manifest);     
     $this->update();
     return true;
  }

  public function deleteCrate() {
    rrmdir($this->bag);
  }


  public function renameCrate($newCrateName) {
    \OCP\Util::writeLog('crate_it', "renameCrate($this->crateName, $newCrateName)", \OCP\Util::DEBUG);
    $oldCrateName = $this->getAbsolutePath($this->crateRoot, $this->crateName);
    $newCrateName = $this->getAbsolutePath($this->crateRoot, $newCrateName);
    rename($oldCrateName, $newCrateName);
  }

  // TODO: If a file has been added to the crate multiple times this will give the wront size
  //       Look at using getFilePaths() instead
  public function getSize() {        
    $files = $this->flatList();
    \OCP\Util::writeLog('crate_it', "Crate::getSize() - Flat list: ".sizeof($files), \OCP\Util::DEBUG);
    $total = 0;
    foreach($files as $file) {
      $total+= filesize($file['filename']);
    }
    return $total;
  }
  
  public function getFlatList() {
      return $this->flatList();
  }
  
  public function getAllFilesAndFolders() { 
      $flat = $this->flatList();   
      \OCP\Util::writeLog('crate_it', "flat: ".serialize($flat), \OCP\Util::DEBUG);    
       
      $res = array();
      foreach ($flat as $elem) {
        $path = $elem['filename']? $elem['filename'] : $elem['folderpath'];
        $absPath = $this->getFullPath($path);
        $res[] = $absPath;
      }
      return $res;
  }
  
  public function packageCrate() {
    $clone = $this->createTempClone();
    $clone->storeFiles();
    $tmpFolder = \OC_Helper::tmpFolder();
    $packagePath = $tmpFolder.'/'.$this->crateName;
    $clone->package($packagePath, 'zip');
    return $packagePath.'.zip';
  }    

  private function createTempClone() {
    $tmpFolder = \OC_Helper::tmpFolder();
    $tmpCrate = new Crate($tmpFolder, $this->crateName);
    $manifest = $this->getManifest();
    $tmpCrate->setManifest($manifest);
    return $tmpCrate;
  }

  public function storeFiles() {
    \OCP\Util::writeLog("crate_it", "Crate::storeFiles() - started", \OCP\Util::DEBUG);
    $files = $this->getFilePaths();
    foreach ($files as $path) {
      $absPath = $this->getFullPath($path);
      $this->addFile($absPath, $path);
    }
    $this->update();
    \OCP\Util::writeLog("crate_it", "Crate::storeFiles() - finished", \OCP\Util::DEBUG);
  }

  private function getFilePaths() {
    $flattened = $this->flatList();
    $result = array();
    foreach ($flattened as $entry) {
      $path = $entry['filename'];
      if($path && !in_array($path, $result)) {
        array_push($result, $path);
      }
    }
    return $result;
  }

  private function flatList() {
      $data = $this->getManifest();
      \OCP\Util::writeLog('crate_it', "Manifest data size: ".sizeof($data), \OCP\Util::DEBUG);    
      
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

  // TODO: There's currently no check for duplicates
  // TODO: root folder has isFolder set, so should other files folders
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
      'id' => 'folder', // TODO: change this to 'folder' => true, need to update js
      'children' => array(),
      'folderpath' => $folder
    );
    $vfsContents = &$vfsEntry['children'];
    $paths = \OC\Files\Filesystem::getDirectoryContent($folder);
    foreach($paths as $path) {
        $relativePath = substr($path['path'], strlen('files/'));
        if (!strncmp($folder, "Shared", 6)) {
            $relativePath = 'Shared/'.$relativePath;
        }
        $this->addPath($relativePath, $vfsContents);
    }
    return $vfsEntry;
  }


  private function addFileToCrate($file) {
    $fullPath = $this->getFullPath($file);
    $id = md5($fullPath);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $fullPath);
    finfo_close($finfo);
    $vfsEntry = array(
      'id' => $id,
      'name' => basename($file),
      'filename' => $file,
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
  
  // TODO: Move to utility class
  private function getFullPath($path) {
    \OCP\Util::writeLog('crate_it', "CrateManager::getFullPath(".$path.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($path);
  }

  private function getAbsolutePath($root, $basename) {
    return $root.'/'.$basename;
  }

}