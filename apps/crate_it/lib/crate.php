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
          'name' => $this->name,
          'folder' => true,
          'children' => array()
        )
      )
    );
    $this->writeFile($this->manifestPath, json_encode($entry));
    $this->update();
  }


  public function getManifest() {
    return $this->readFile($this->manifestPath);
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

}