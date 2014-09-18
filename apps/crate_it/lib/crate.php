<?php

namespace OCA\crate_it\lib;

require '3rdparty/BagIt/bagit.php';
use BagIt;
use \OC\Files\Filesystem;

class Crate extends BagIt {

  private $manifestPath;
  private $crateName;
  private $crateRoot;

  public function __construct($crateRoot, $crateName, $description='') {
      \OCP\Util::writeLog('crate_it', "Crate::__construct(".$crateRoot.','.$crateName.','.$description.")", \OCP\Util::DEBUG);
      parent::__construct($this->getAbsolutePath($crateRoot, $crateName), true, false, false, null);     
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
    file_put_contents($this->manifestPath, json_encode($entry));
    $this->update();
  }
  
  // TODO: Convenience function for development, remove
  //       in production release?
  public function getReadme($twig) {
    $this->createReadme($twig);
    $readmePath = $this->getDataDirectory()."/README.html";
    return file_get_contents($readmePath);
  }

  // TODO: Attempt to get own reference to TWIG rather than have it
  //       passed as a parameter
  // TODO: Since the file is created directly, it bypasses the manifest,
  //       is that what we want?
  private function createReadme($twig) {
    $manifest = $this->getManifest();
    $manifest['crate_name'] = $this->crateName;
    $manifest['files'] = $this->flatList();
    $manifest['creators'] = $this->isCreatorIdUrl($manifest['creators']);
    date_default_timezone_set('Australia/Sydney');    
    $manifest['created_date'] = date("Y-m-d H:i:s");   
    $manifest['created_date_formatted'] = date("F jS, Y - H:i:s (T)");
    $vfs = &$manifest['vfs'][0];
    $manifest['filetree'] = $this->buildFileTreeFromRoot($vfs);
    $git_res = $this->getVersion();
    $release = $git_res[0];
    $commit = $git_res[2];
    $manifest['version'] = "Release $release at commit $commit.";
    $htmlStr = $twig->render('readme.php', $manifest);
    $readmePath = $this->getDataDirectory()."/README.html";
    file_put_contents($readmePath, $htmlStr);
  }

  // NOTE: workaround for non-functioning twig operators 'starts with' and 'matches'
  private function isCreatorIdUrl($creators) {
    $protocol = '/^https?\:\/\//';
    foreach ($creators as &$creator) {
      $uri =$creator['overrides']['identifier'];
      $orUri = $creator['identifier'];
      if(!empty($orUri) && preg_match($protocol, $orUri)) {
        $creator['url'] = true;
      } elseif(!empty($uri) && preg_match($protocol, $uri)) {
        $creator['url'] = true;
      }
    }
    // var_dump($creators);
    return $creators;
  }

  private function getVersion() {
    exec('git --git-dir=/home/devel/owncloud/.git --work-tree=/home/devel/owncloud describe --tags', $git);
    return explode('-', $git[0]);
  }
  
  private function buildFileTreeFromRoot($rootnode) {
      $tree = '';
      // if we get to this point, then the crate has files for sure, so no need to 
      // check if children exists
      $children = $rootnode['children'];
      foreach($children as $child) {
            $tree = $this->buildFileTreeHtml($child, $tree);
      }
      return '<ul>'.$tree.'</ul>';
  }
  
  private function buildFileTreeHtml($node, $tree='') {           
    if ($node['id'] == 'folder')
    {
        $text = $node['name'];
        $tree = $tree."<li>$text</li><ul>";
        $children = $node['children'];     
        foreach($children as $child) {
            $tree = $this->buildFileTreeHtml($child, $tree);
        }
        $tree = $tree.'</ul>';
    }
    else {
        // substitute ' ' with '_', since the downloaded files don't have
        // ' ' in the name
        $text = str_replace(' ', '_', $node['name']);
        // change the filename part of the path to the 'underscored' version
        $filename = str_replace($node['name'], $text, $node['filename']);
        $rootfolder = $this->getRootFolderName();
        if ($node['valid'] == 'true')
        {
            $tree = $tree."<li><a href='./$rootfolder/$filename'>$text</a></li>";  
        } else {
            $tree = $tree."<li>$text (invalid)</li>";  
        }
        
    }
    return $tree;
  }

  public function getManifest() {
    $manifest = file_get_contents($this->manifestPath);
    return json_decode($manifest, true);
  }

  public function setManifest($manifest) {
    $manifest = json_encode($manifest);
    file_put_contents($this->manifestPath, $manifest);
  }

  public function addToCrate($path) {
    \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$path.")", \OCP\Util::DEBUG);
    $manifest = $this->getManifest();
    $vfs = &$manifest['vfs'][0];
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
    $files = $this->getAllFilesAndFolders();
    \OCP\Util::writeLog('crate_it', "Crate::getSize() - Flat list: ".sizeof($files), \OCP\Util::DEBUG);
    $total = 0;
    $checked = array();
    foreach($files as $file) {
      \OCP\Util::writeLog('crate_it', "Crate::getSize() - checking: ".$file, \OCP\Util::DEBUG);        
      if (!in_array($file , $checked)) {
          $total+= filesize($file);
      } 
      $checked[] = $file;
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
  
  public function packageCrate($twig) {
    $clone = $this->createTempClone();
    $clone->createReadme($twig);
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

  private function getRootFolderName() {
    // get root folder
    $manifest = $this->getManifest();
    $vfs = &$manifest['vfs'][0];
    $rootfolder = $vfs['name'];  
    \OCP\Util::writeLog("crate_it", "Crate::getRootFolderName() - $rootfolder", \OCP\Util::DEBUG);
    return $rootfolder;
  }
  
  public function storeFiles() {
    \OCP\Util::writeLog("crate_it", "Crate::storeFiles() - started", \OCP\Util::DEBUG);
    $files = $this->getFilePaths();
    foreach ($files as $path) {
      $absPath = $this->getFullPath($path);
      $pathInsideRootFolder = $this->getRootFolderName().'/'.$path;
      $this->addFile($absPath, $pathInsideRootFolder);
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
    \OCP\Util::writeLog('crate_it', "Crate::addPath(".$path.")", \OCP\Util::DEBUG);
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
  
  public function generateEPUB($twig) {
    \OCP\Util::writeLog('crate_it', "Crate::generateEPUB(".$path.")", \OCP\Util::DEBUG);
    $files = $this->getPreviewPaths();
    $params = array('files' => $files);
    $epub = $twig->render('epub.php', $params);
    $tmpFolder = \OC_Helper::tmpFolder();
    $htmlPath = $tmpFolder.'/'.$this->crateName.'.html';
    file_put_contents($htmlPath, $epub);
    $htmlPath = str_replace(' ', '\ ', $htmlPath);
    $epubPath = $tmpFolder.'/'.$this->crateName.'.epub';
    $command = "ebook-convert $htmlPath $epubPath --no-default-epub-cover --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3";
    exec($command, $retval);
    return $epubPath;
  }

  private function getPreviewPaths() {
    $files = $this->flatList();
    $result = array();
    foreach($files as $file) {
      $path = Filesystem::getLocalFile($file['filename']);
      $pathInfo = pathinfo($path);
      $previewPath = $pathInfo['dirname'].'/_html/'.$pathInfo['basename'].'/index.html';
      if(file_exists($previewPath)) {
        $file['preview'] = $previewPath;
        array_push($result, $file);
      }
    }
    return $result;
  }

  // TODO: Get rid of this and just import \OC\Files\Filesystem
  private function getFullPath($path) {
    \OCP\Util::writeLog('crate_it', "Crate::getFullPath(".$path.")", \OCP\Util::DEBUG);
    return \OC\Files\Filesystem::getLocalFile($path);
  }

  private function getAbsolutePath($root, $basename) {
    return $root.'/'.$basename;
  }
  
  public function getManifestFileContent() {
      return file_get_contents($this->manifestPath);
  }

}