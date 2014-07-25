<?php

namespace OCA\crate_it\lib;

require 'apps/crate_it/3rdparty/BagIt/bagit.php';
use BagIt;

class Crate extends BagIt {

  private $manifestPath;
  private $crateName;

  public function __construct($crateRoot, $crateName, $description='') {
      \OCP\Util::writeLog('crate_it', "Crate::__construct(".$crateRoot.','.$crateName.','.$description.")", \OCP\Util::DEBUG);
      if (!file_exists($crateRoot)) {
          mkdir($crateRoot, 0755, true);
      }
      parent::__construct($crateRoot.'/'.$crateName);     
      $this->crateName = $crateName;
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

  public function setField($field, $value) {
    $manifest = getManifest();
    $manifest[$field] = $value;
    $this->setManifest($manifest);
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
  
  /**
   * NOTE: this currently just update VFS! Change it to update everything including
   * description and metadata?
   */
  public function updateCrate($data) {
     \OCP\Util::writeLog("crate_it", "Crate::updateCrate()", \OCP\Util::DEBUG);
     $new_vfs = json_decode($data);
     // read the manifest content and insert new vfs
     $manifest = $this->getManifest();
     $manifest['vfs'] = $new_vfs;
     $this->setManifest($manifest);     
     $this->update();
     return true;
  }

  public function getSize() {        
    $files = $this->flatList();
    // \OCP\Util::writeLog('crate_it', "Crate::getSize() - Flat list: ".sizeof($files), 3);
    $total = 0;
    foreach($files as $file) {
      $total+= filesize($file['filename']);
    }
    return $total;
  }
  
  public function flatList() {
        $data = $this->getManifest();
        \OCP\Util::writeLog('crate_it', "Manifest data size: ".sizeof($data),3);    
        
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
      'children' => array()
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
  
  // TODO: Move to utility class
  private function getFullPath($path) {
    \OCP\Util::writeLog('crate_it', "CrateManager::getFullPath(".$path.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($path);
  }

}