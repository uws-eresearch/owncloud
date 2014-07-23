<?php

namespace OCA\crate_it\Manager;

require 'apps/crate_it/3rdparty/BagIt/bagit.php';

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
    public function createCrate($crate_id) {
        \OCP\Util::writeLog('crate_it', "CrateManager::createCrate(".$crate_id.")", 3);
        // creates the bag and return the manifest file path
        $manifest_path = $this->getManifestPath($crate_id); 
        $this->createManifest($crate_id, $manifest_path);
        // TODO: validate and throws exception
    }
    
    private function getManifestPath($crate_id) {
        $bag = $this->getOrCreateBag($crate_id);
        $data_dir = $bag->getDataDirectory();
        $manifest_path = $data_dir . '/manifest.json';
        \OCP\Util::writeLog('crate_it', "Manifest path for ".$crate_id." is: ".$manifest_path, 3);
        return $manifest_path;
    }
    
    private function getOrCreateBag($crate_id) {
        $user_id = $this->api->getUserId();
        $crate_dir = $this->getCrateRoot($user_id) . '/' . $crate_id;
        return new \BagIt($crate_dir); // create new bag or return existing bag
    }

    public function getCrateList() {
        $user_id = $this->api->getUserId();
        \OCP\Util::writeLog("crate_it", 'Getting a list of crates for user: '.$user_id, \OCP\Util::DEBUG);
        
        $cratelist = array();
        if ($handle = opendir($this->getCrateRoot($user_id))) {
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
    
    public function getCrateFiles($crate_id) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateFiles(".$crate_id.")", 3);
        $contents = $this->getManifestData($crate_id);
        return json_encode($contents['vfs']);
    }

    private function getCrateRoot($user_id) {
        $base_dir = \OC::$SERVERROOT . '/data/' . $user_id;
        return $base_dir . '/crates';
    }
    
    private function createManifest($crate_id, $manifest_path) {
    if (!file_exists($manifest_path)) {
      $fp = fopen($manifest_path, 'x');
      $entry = array(
        'description' => '',
        'creators' => array() ,
        'activities' => array() ,
        'vfs' => array(
          array(
            'id' => 'rootfolder',
            'name' => $crate_id,
            'folder' => true,
            'children' => array()
          )
        )
      );
      fwrite($fp, json_encode($entry));
      fclose($fp);
      $this->getOrCreateBag($crate_id)->update();
    }
  }
    
    public function getManifestData($crate_id)
    {
        $manifest_path = $this->getManifestPath($crate_id);
        // read from manifest
        $fp = fopen($manifest_path, 'r');
        $contents = file_get_contents($manifest_path);
        $cont_array = json_decode($contents, true);
        fclose($fp);
        return $cont_array;
    }

    public function addToCrate($crate_id, $file)
    {
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
        $manifest_path = $this->getManifestPath($crate_id);
    
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
        return "File added to the crate " . $crate_id;
    }

    // TODO: There's currently no check for duplicates
    // TODO: root folder has isFolder set, so should other files folders
    private function addPath($path, &$vfs) {
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
        return \OC\Files\Filesystem::getLocalFile($file);
    }
    
    public function updateCrate($crate_id, $data) {
        
        \OCP\Util::writeLog('crate_it', "CrateManager::UpdateCrate(). Updating crate: ".$crate_id, 3);
        
        $new_vfs = json_decode($data);
        \OCP\Util::writeLog('crate_it', "Crate data: ".$new_vfs, 3);
        
        $manifest_path = $this->getManifestPath($crate_id);
        $contents = json_decode(file_get_contents($manifest_path), true);
        $fp = fopen($manifest_path, 'w+');
        $contents['vfs'] = $new_vfs;
        fwrite($fp, json_encode($contents));
        fclose($fp);
        $this->getOrCreateBag($crate_id)->update();
        return true;
    }

    public function getCrateSize($crate_id) {
        \OCP\Util::writeLog('crate_it', "CrateManager::getCrateSize(). Crate: ".$crate_id, 3);
        
        $files = $this->flatList($crate_id);
        $total = 0;
        foreach($files as $file) {
          $total+= filesize($file['filename']);
        }
        \OCP\Util::writeLog('crate_it', "Crate size: ".$total, 3);
        $data = array('size' => $total, 'human' => \OCP\Util::humanFileSize($total));
        return $data;
    }
    
    public function flatList($crate_id) {
        $data = $this->getManifestData($crate_id);
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
    
}
