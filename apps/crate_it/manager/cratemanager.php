<?php

namespace OCA\crate_it\Manager;

use OCA\crate_it\lib\Crate;

class CrateManager {

    /**
     * @var API
     */
    private $api;

    /**
     * @var twig
     */
    private $twig;
    
    public function __construct($api, $twig){
        $this->api = $api;
        $this->twig = $twig;
        if ($api->isLoggedIn()) {
            $this->ensureDefaultCrateExists();
            $this->ensureCrateIsSelected();
        }
    }
    

    // TODO: getCrate and createCrate do just about the same thing, perhaps they can be rolled into one
    public function createCrate($crateName, $description) {
        \OCP\Util::writeLog('crate_it', "CrateManager::createCrate($crateName, $description)", \OCP\Util::DEBUG);
        $crateRoot = $this->getCrateRoot();
        new Crate($crateRoot, $crateName, $description);
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
        $contents = $this->getManifestData($crateName);
        return json_encode($contents['vfs']);
    }

    private function ensureDefaultCrateExists() {
        $crateRoot = $this->getCrateRoot();
        if (!file_exists($crateRoot)) {
            mkdir($crateRoot, 0755, true);
        }
        $crateList = $this->getCrateList();
        if(empty($crateList)) {
            $this->createCrate('default_crate', '');
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
        return $crate->getReadme($this->twig);
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
        $crate = $this->getCrate($crateName);
        return $crate->packageCrate($this->twig);
    }

    public function generateEPUB($crateName){
        \OCP\Util::writeLog('crate_it', "CrateManager::generateEPUB() - ".$crateName, \OCP\Util::DEBUG);
        $crate = $this->getCrate($crateName);
        return $crate->generateEPUB($this->twig);
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
                $node['valid'] = var_export($valid, true);
                $crate->setManifest($manifest);                   
            }
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
        $res = array();
        foreach ($files as $filepath) {
            \OCP\Util::writeLog('crate_it', "CrateManager::checkCrate() - checking ".$filepath, \OCP\Util::DEBUG);
            $file_exist = file_exists($filepath); 
            if (!$file_exist) {
                $res[basename($filepath)] = $file_exist; 
            }
        }
        $this->updateCrateCheckIcons($crateName);
        return $res;
    }
    
    public function getManifestFileContent($crateName) {
        $crate = $this->getCrate($crateName);
        return $crate->getManifestFileContent();
    }
    
}
