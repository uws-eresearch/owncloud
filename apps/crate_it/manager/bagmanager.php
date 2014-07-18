<?php

namespace OCA\crate_it\Manager;

class BagManager {
    
    public function __construct(){
         \OCP\Util::writeLog('filemanager', "created!", 3);       
    }

    public function addToBag($file)
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
    
        $contents = json_decode(file_get_contents($this->manifest) , true); // convert it to an array.
        $vfs = & $contents['vfs'][0];
        if (array_key_exists('children', $vfs)) {
          $vfs = & $vfs['children'];
        }
        else {
          $vfs['children'] = array();
          $vfs = & $vfs['children'];
        }
    
        $this->addPath($file, $vfs);
        $fp = fopen($this->manifest, 'w');
        fwrite($fp, json_encode($contents));
        fclose($fp);
    
        // update the hashes
    
        $this->bag->update();
        return "File added to the crate " . $this->selected_crate;
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
}
