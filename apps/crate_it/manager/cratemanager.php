<?php

namespace OCA\crate_it\Manager;

use OCA\crate_it\lib\Crate;
use OCA\crate_it\lib\Util;

class CrateManager {
    
    public function __construct(){
        if (\OCP\User::isLoggedIn()) {
            $this->ensureDefaultCrateExists();
            $this->ensureCrateIsSelected();
        }
    }
    

    // TODO: getCrate and createCrate do just about the same thing, perhaps they can be rolled into one
    public function createCrate($crateName, $description, $data_retention_period) {
        \OCP\Util::writeLog('crate_it', "CrateManager::createCrate($crateName, $description, $data_retention_period)", \OCP\Util::DEBUG);
        $crateRoot = $this->getCrateRoot();
        new Crate($crateRoot, $crateName, $description, $data_retention_period);
        // TODO: Just returns a parameter that was passed?
        return $crateName;
    }
    
    /**
     * Create the bag with manifest file for the crate
     * Throws exception when fail
     */
    public function getCrate($crateName) {
      \OCP\Util::writeLog('crate_it', "CrateManager::getCrate(".$crateName.")", \OCP\Util::DEBUG);
      $crateRoot = $this->getCrateRoot();
      if (!file_exists($crateRoot.'/'.$crateName)) {
        throw new \Exception("Crate $crateName not found");
      }
      return new Crate($crateRoot, $crateName);
    }

    public function getCrateList() {
        \OCP\Util::writeLog("crate_it", 'CrateManager::getCrateList()', \OCP\Util::DEBUG);
        $cratelist = array();
        $crateRoot = $this->getCrateRoot();
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
        $contents = $this->getManifest($crateName);
        return json_encode($contents['vfs']);
    }

    public function createMetadata($crateName) {
        $crate = $this->getCrate($crateName);
        return $crate->createMetadata();
    }

    private function ensureDefaultCrateExists() {
        $crateRoot = $this->getCrateRoot();
        if (!file_exists($crateRoot)) {
            mkdir($crateRoot, 0755, true);
        }
        $crateList = $this->getCrateList();
        if(empty($crateList)) {
            $this->createCrate('default_crate', '','Perpetuity');
        }
    }

    private function ensureCrateIsSelected() {
        $crateList = $this->getCrateList();
        if (!in_array($_SESSION['selected_crate'], $crateList)) {
            if (in_array('default_crate', $crateList)) {
                $_SESSION['selected_crate'] = 'default_crate';
                session_commit();
            } else {
                $_SESSION['selected_crate'] = $crateList[0];               
                session_commit();
            }
        }
    }

    public function getReadme($crateName) {
        $crate = $this->getCrate($crateName);
        return $crate->getReadme();
    }

    private function getCrateRoot() {        
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateRoot()", \OCP\Util::DEBUG);
        return Util::joinPaths(Util::getUserPath(), 'crates');
    }

    public function getManifest($crateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getManifest(".$crateName.")", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        return $crate->getManifest();
    }

    public function addToCrate($crateName, $path) {
        \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$crateName.','.$path.")", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        $crate->addToCrate($path);
    }

    public function getCrateSize($crateName) {
        $crate = $this->getCrate($crateName);
        $total = $crate->getSize();   
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateSize() - Crate size: ".$total, \OCP\Util::DEBUG);
        $data = array('size' => $total, 'human' => \OCP\Util::humanFileSize($total));
        return $data; 
    }
    
    public function updateCrate($crateName, $field, $value) {
        $crate = $this->getCrate($crateName);
        $crate->updateCrate($field, $value);
    }

    public function deleteCrate($crateName) {
        $crate = $this->getCrate($crateName);
        $crate->deleteCrate();
        $this->ensureDefaultCrateExists();
        $this->ensureCrateIsSelected();
    }

    public function renameCrate($crateName, $newCrateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::renameCrate($crateName, $newCrateName)", \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        $crate->renameCrate($newCrateName);
    }
    
    public function packageCrate($crateName){
        $this->updateCrateCheckIcons($crateName);
        $crate = $this->getCrate($crateName);
        $tempdir = Util::joinPaths(Util::getTempPath(), \OCP\User::getUser());
        if (!file_exists($tempdir)) {
            mkdir($tempdir, 0755, true);
        }
        return $crate->packageCrate($tempdir);
    }

    public function generateEPUB($crateName){
        \OCP\Util::writeLog('crate_it', "CrateManager::generateEPUB() - ".$crateName, \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        return $crate->generateEPUB();
    }
    
    private function updateCrateCheckIcons($crateName) {        
        $crate = $this->getCrate($crateName);
        $manifest = $crate->getManifest();
        $rootfolder = &$manifest['vfs'][0];
        $children = &$rootfolder['children'];    
        if ($children == null) {
            $children = array();
            $rootfolder['valid'] = var_export(true, true);
            $crate->setManifest($manifest);
        }   
        $valid = true;
        foreach ($children as &$child) {
            $childValid = $this->validateNode($child, $crate, $manifest);
            $valid =$valid && $childValid;
            $rootfolder['valid'] = var_export($valid, true);
            $crate->setManifest($manifest);
        }       
        \OCP\Util::writeLog('crate_it', "CrateManager::validateNode() - rootfolder is ".var_export($valid, true), \OCP\Util::DEBUG); 
        
    }
    
    private function validateNode(&$node, $crate, &$manifest) {
        \OCP\Util::writeLog('crate_it', "CrateManager::validateNode() - ".$node['name'], \OCP\Util::DEBUG); 
        $valid = true;
        if ($node['id'] == 'folder') {
            $children = &$node['children'];
            foreach($children as &$child) {
                $childValid = $this->validateNode($child, $crate, $manifest);
                $valid = $valid && $childValid;                            
            }
            $node['valid'] = var_export($valid, true);
            $crate->setManifest($manifest);       
        }
        else {
            $filename = $node['filename'];
            $filepath = \OC\Files\Filesystem::getLocalFile($filename);
            $valid = file_exists($filepath); 
            $node['valid'] = var_export($valid, true);
            $crate->setManifest($manifest);
        }
        \OCP\Util::writeLog('crate_it', "CrateManager::validateNode() - ".$node['name']." is ".var_export($valid, true), \OCP\Util::DEBUG); 
        
        return $valid;
    }

    public function checkCrate($crateName) {
        \OCP\Util::writeLog('crate_it', "CrateManager::checkCrate() - ".$crateName, \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        $files = $crate->getAllFilesAndFolders();
        $result = array();
        foreach ($files as $filepath) {
            \OCP\Util::writeLog('crate_it', "CrateManager::checkCrate() - checking ".$filepath, \OCP\Util::DEBUG);
            $file_exist = \OC\Files\Filesystem::file_exists($filepath);
            if (!$file_exist) {
                $result[basename($filepath)] = $file_exist;
            }
        }
        $this->updateCrateCheckIcons($crateName);
        return $result;
    }
    
    public function getManifestFileContent($crateName) {
        $crate = $this->getCrate($crateName);
        return $crate->getManifestFileContent();
    }
    
}
