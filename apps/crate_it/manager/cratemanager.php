<?php

namespace OCA\crate_it\Manager;

// require 'apps/crate_it/3rdparty/BagIt/bagit.php';
require 'apps/crate_it/lib/crate.php';

use OCA\crate_it\lib\Crate;

class CrateManager {

    /**
     * @var API
     */
    private $api;
    
    public function __construct($api){
        $this->api = $api;
    }
    
    /**
     * Create the bag with manifest file for the crate
     * Throws exception when fail
     */
    // public function createCrate($crateName, $description) {
    //     \OCP\Util::writeLog('crate_it', "CrateManager::createCrate(".$crateName.")", 3);
    //     // creates the bag and return the manifest file path
    //     $manifest_path = $this->getManifestPath($crateName); 
    //     $this->createManifest($crateName, $manifest_path);
    //     // TODO: validate and throws exception
    // }
    

    private function getCrate($crateName) {
      \OCP\Util::writeLog('crate_it', "CrateManager::getCrate(".$crateName.")", \OCP\Util::DEBUG);
      $userId = $this->api->getUserId();
      $cratePath = $this->getCrateRoot($userId).'/'.$crateName;
      return new Crate($cratePath);
    }

    // private function getManifestPath($crateName) {
    //     \OCP\Util::writeLog('crate_it', "CrateManager::getManifestPath(".$crateName.")", \OCP\Util::DEBUG);
    //     $bag = $this->getOrCreateBag($crateName);
    //     $dataDir = $bag->getDataDirectory();
    //     // \OCP\Util::writeLog('crate_it', 'Data dir: '.$dataDir, 3);
    //     $manifestPath = $dataDir . '/manifest.json';
    //     $manifestPath = stripslashes($manifestPath);
    //     return $manifestPath;
    // }
    
    // private function getOrCreateBag($crateName) {
    //     $userId = $this->api->getUserId();
    //     $crateDir = $this->getCrateRoot($userId) . '/' . $crateName;
    //     return new \BagIt($crateDir); // create new bag or return existing bag
    // }

    public function getCrateList() {
        $userId = $this->api->getUserId();
        \OCP\Util::writeLog("crate_it", 'CrateManager::getCrateList(), for: '.$userId, \OCP\Util::DEBUG);
        
        $cratelist = array();
        $crateRoot = $this->getCrateRoot($userId);
        if(!file_exists($crateRoot)) {
          mkdir($crateRoot);
        }
        if ($handle = opendir($crateRoot)) {
            $filteredlist = array('.', '..', 'packages', '.Trash');
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, $filteredlist)) {
                    array_push($cratelist, $file);
                }
            }
            closedir($handle);
        }
        return $cratelist;
    }
    
    public function getCrateFiles($crateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateFiles(".$crateName.")", \OCP\Util::DEBUG);
        $contents = $this->getManifestData($crateName);
        return json_encode($contents['vfs']);
    }

    private function getCrateRoot($userId) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateRoot(".$userId.")", \OCP\Util::DEBUG);
        $baseDir = \OC::$SERVERROOT.'/data/'.$userId;
        return $baseDir.'/crates';
    }
    
    // public function createCrate($crateName, $description) {
    //   \OCP\Util::writeLog('crate_it', "CrateManager::createCrate(".$crateName.','.$description.")", \OCP\Util::DEBUG);
    //   // creates the bag and return the manifest file path
    //   $manifestPath = $this->getManifestPath($crateName); 
    //   if (!file_exists($manifestPath)) {
    //     $entry = array(
    //       'description' => $description,
    //       'creators' => array() ,
    //       'activities' => array() ,
    //       'vfs' => array(
    //         array(
    //           'id' => 'rootfolder',
    //           'name' => $crateName,
    //           'folder' => true,
    //           'children' => array()
    //         )
    //       )
    //     );
    //     $this->writeToFile($manifestPath, json_encode($entry));
    //     $this->getOrCreateBag($crateName)->update();
    //   }
    // }
    
    // TODO: Make the method names consistant across router/controller/service api
    // public function getManifestData($crateName) {
    //     \OCP\Util::writeLog('crate_it', "CrateManager::getManifestData(".$crateName.")", \OCP\Util::DEBUG);
    //     $manifest_path = $this->getManifestPath($crateName);
    //     // \OCP\Util::writeLog("crate_it", 'Manifest path: '.$manifest_path, \OCP\Util::DEBUG);
    //     // \OCP\Util::writeLog('crate_it', "Manifest for ".$crateName." is: ".$manifest_path, 3);
    //     // read from manifest
    //     $fp = fopen($manifest_path, 'r');
    //     $contents = file_get_contents($manifest_path);
    //     $cont_array = json_decode($contents, true);
    //     fclose($fp);
    //     return $cont_array;
    // }

    public function getManifestData($crateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getManifestData(".$crateName.")", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        $manifest = $crate->getManifest();
        return json_decode($manifest, true);
    }

    public function addToCrate($crateName, $file) {
        \OCP\Util::writeLog('crate_it', "CrateManager::addToCrate(".$crateName.','.$file.")", \OCP\Util::DEBUG);
        $path_parts = pathinfo($file);
        $filename = $path_parts['filename'];
        if (\OC\Files\Filesystem::isReadable($file)) {  
          //do nothing?
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
        $manifest_path = $this->getManifestPath($crateName);
    
        $contents = json_decode(file_get_contents($manifest_path) , true); // convert it to an array.
        $vfs = & $contents['vfs'][0];
        if (array_key_exists('children', $vfs)) {
          $vfs = & $vfs['children'];
        }
        else {
          $vfs['children'] = array();
          $vfs = & $vfs['children'];
        }
    
        $this->addPath($file, $vfs);
        $fp = fopen($manifest_path, 'w');
        fwrite($fp, json_encode($contents));
        fclose($fp);
    
        // update the hashes
        $this->getOrCreateBag()->update();
        return "File added to the crate " . $crateName;
    }

   // TODO: There's currently no check for duplicates
   // TODO: root folder has isFolder set, so should other files folders
    // TODO: refactor to two helpers, addFolder and addFile
    private function addPath($path, &$vfs) {
        \OCP\Util::writeLog('crate_it', "CrateManager::addPath(".$path.")", \OCP\Util::DEBUG);
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

  private function getFullPath($file) {
    \OCP\Util::writeLog('crate_it', "CrateManager::getFullPath(".$file.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($file);
  }

  private function writeToFile($path, $contents) {
    \OCP\Util::writeLog('crate_it', "CrateManager::writeToFile(".$path.")", \OCP\Util::DEBUG);
    $fp = fopen($path, 'w');
    fwrite($fp, $contents);
    fclose($fp);
  }

}
