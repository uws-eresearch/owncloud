<?php

namespace OCA\crate_it\Manager;

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
    

    // TODO: getCrate and createCrate do just about the same thing, perhaps they can be rolled into one
    public function createCrate($crateName, $description) {
        \OCP\Util::writeLog('crate_it', "CrateManager::createCrate(".$crateName.")", \OCP\Util::DEBUG);
        $crateRoot = $this->getCrateRoot();
        new Crate($crateRoot, $crateName, $description);
    }
    
    /**
     * Create the bag with manifest file for the crate
     * Throws exception when fail
     */
    private function getCrate($crateName) {
      \OCP\Util::writeLog('crate_it', "CrateManager::getCrate(".$crateName.")", \OCP\Util::DEBUG);
      $crateRoot = $this->getCrateRoot();
      return new Crate($crateRoot, $crateName);
    }

    public function getCrateList() {
        \OCP\Util::writeLog("crate_it", 'CrateManager::getCrateList()', \OCP\Util::DEBUG);
        $cratelist = array();
        $crateRoot = $this->getCrateRoot();
        // TODO: Should this check be somewhere else?
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

    private function getCrateRoot() {        
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateRoot()", \OCP\Util::DEBUG);
        $userId = $this->api->getUserId();
        $baseDir = \OC::$SERVERROOT.'/data/'.$userId;
        return $baseDir.'/crates';
    }

    public function getManifestData($crateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getManifestData(".$crateName.")", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        return $crate->getManifest();
    }

    public function addToCrate($crateName, $path) {
      \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$crateName.','.$path.")", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        $crate->addToCrate($path);
        return 'Added to crate '.$crateName;
    }

    public function getCrateSize($crateName) {
        $crate = $this->getCrate($crateName);
        $total = $crate->getSize();   
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateSize() - Crate size: ".$total, 3);      
        $data = array('size' => $total, 'human' => \OCP\Util::humanFileSize($total));
        return $data; 
    }
    
}
