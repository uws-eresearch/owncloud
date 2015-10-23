<?php

namespace OCA\crate_it\lib;

require '3rdparty/BagIt/bagit.php';
use BagIt;
use OC\Files\Filesystem;
//use OCP\Util;

class Crate extends BagIt {

    private $manifestPath;
    private $crateName;
    private $crateRoot;

    public function __construct($crateRoot, $crateName, $description = '', $data_retention_period = '') {
        \OCP\Util::writeLog('crate_it', "Crate::__construct(".$crateRoot.','.$crateName.','.$description.','.$data_retention_period.")", \OCP\Util::DEBUG);
        $this->crateName = $crateName;
        $this->crateRoot = $crateRoot;
        parent::__construct($this->getAbsolutePath($crateName), true, false, false, null);
        $this->manifestPath = Util::joinPaths($this->getDataDirectory(), 'manifest.json');
        if(!file_exists($this->manifestPath)) {
            $this->createManifest($description, $data_retention_period);
        }
    }

    private function createManifest($description,$data_retention_period) {
        \OCP\Util::writeLog('crate_it', "Crate::createManifest(".$description.",".$data_retention_period.")", \OCP\Util::DEBUG);
        $description = NULL ? '' : $description;
        $data_retention_period = NULL ? '': $data_retention_period;
        $entry = array(
            'description' => $description,
            'data_retention_period' => $data_retention_period,
            'submitter' => array(
                'email' => \OCP\Config::getUserValue(\OCP\User::getUser(), 'settings', 'email', ''),
                'displayname' => \OCP\User::getDisplayName(),
            ),
            'creators' => array(),
            'activities' => array(),
            'vfs' => array(
                array(
                    'id' => 'rootfolder',
                    'name' => $this->crateName,
                    'folder' => true,
                    'children' => array()
                )
            )
        );
        $this->setManifest($entry);
        $this->update();
    }

    public function getReadme() {
        $readmePath = $this->getDataDirectory()."/README.html";
        return file_get_contents($readmePath);
    }

    private function createReadme() {
        $metadata = $this->createMetadata();
        $html = Util::renderTemplate('readme', $metadata);
        $readmePath = $this->getDataDirectory()."/README.html";
        file_put_contents($readmePath, $html);
    }

    public function createMetadata() {
        $metadata = $this->getManifest();
        $metadata['crate_name'] = $this->crateName;
        $metadata['files'] = $this->flatList();
        $metadata['creators'] = $this->isCreatorIdUrl($metadata['creators']);
        // TODO: Update to use utility method
        date_default_timezone_set('Australia/Sydney');
        $metadata['created_date'] = Util::getTimestamp("Y-m-d H:i:s");
        $metadata['created_date_formatted'] = Util::getTimestamp("F jS, Y - H:i:s (T)");
        $vfs = &$metadata['vfs'][0];
        $metadata['filetree'] = $this->buildFileTreeFromRoot($vfs);
        $metadata['version'] = "Version ".\OCP\App::getAppVersion('crate_it');
        return $metadata;
    }

    // NOTE: workaround for non-functioning twig operators 'starts with' and 'matches'
    private function isCreatorIdUrl($creators) {
        $protocol = '/^https?\:\/\//';
        foreach($creators as &$creator) {
            $identifier = $creator['identifier'];
            if(!empty($identifier) && preg_match($protocol, $identifier)) {
                $creator['url'] = true;
            }
        }
        return $creators;
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

    private function buildFileTreeHtml($node, $tree = '') {
        if($node['id'] == 'folder') {
            $text = $node['name'];
            $tree = $tree."<li>$text</li><ul>";
            $children = $node['children'];
            foreach($children as $child) {
                $tree = $this->buildFileTreeHtml($child, $tree);
            }
            $tree = $tree.'</ul>';
        } else {
            // substitute ' ' with '_', since the downloaded files don't have
            // ' ' in the name
            $text = str_replace(' ', '_', $node['name']);
            // change the filename part of the path to the 'underscored' version
            $filename = str_replace($node['name'], $text, $node['filename']);
            $rootfolder = $this->getRootFolderName();
            if($node['valid'] == 'true') {
                $tree = $tree."<li><a href='./$rootfolder/$filename'>$text</a></li>";
            } else {
                $tree = $tree."<li>$text (invalid)</li>";
            }
        }
        return $tree;
    }

    public function getManifest() {
        $manifest = file_get_contents($this->manifestPath);
        if($manifest) {
            $result = json_decode($manifest, true);
        }
        if(!$manifest or is_null($result)) {
            throw new \Exception("Error opening manifest.json");
        }
        return $result;
    }

    public function setManifest($manifest) {
        $manifest = json_encode($manifest);
        $success = file_put_contents($this->manifestPath, $manifest);
        if($manifest === false || $success === false) {
            throw new \Exception("Error writing to manifest.json");
        }
    }

    public function addToCrate($path) {
        \OCP\Util::writeLog('crate_it', "Crate::addToCrate(".$path.")", \OCP\Util::DEBUG);
        $manifest = $this->getManifest();
        $vfs = &$manifest['vfs'][0];
        if(array_key_exists('children', $vfs)) {
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
        \OCP\Util::writeLog("crate_it", "Crate::updateCrate($field, ".$value.")", \OCP\Util::DEBUG);
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
        $oldCrateName = $this->getAbsolutePath($this->crateName);
        $newCrateName = $this->getAbsolutePath($newCrateName);
        $success = rename($oldCrateName, $newCrateName);
        if(!$success) {
            throw new \Exception("Error renaming crate");
        }
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
            if(!in_array($file, $checked)) {
                $total += \OC\Files\Filesystem::filesize($file);
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
        foreach($flat as $elem) {
            $path = $elem['filename'] ? $elem['filename'] : $elem['folderpath'];
            $res[] = $path;
        }
        return $res;
    }

    public function packageCrate($tmpFolder) {
        $clone = $this->createTempClone();
        $clone->createReadme();
        $clone->storeFiles();
        $packagePath = Util::joinPaths($tmpFolder, $this->crateName);
        if(file_exists($packagePath.'.zip')) {
            unlink($packagePath.'.zip');
        }
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
        foreach($files as $path) {
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
        foreach($flattened as $entry) {
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
        if(count($vfs) > 0) {
            foreach($vfs as $entry) {
                if(array_key_exists('filename', $entry)) {
                    $flat_entry = array(
                        'id' => $entry['id'],
                        'path' => $path,
                        'name' => $entry['name'],
                        'filename' => $entry['filename'],
                        'size' => $entry['size']
                    );
                    array_push($flat, $flat_entry);
                } elseif(array_key_exists('children', $entry)) {
                    $this->flat_r($entry['children'], $flat, $path.$entry['name'].'/');
                }
            }
        }
    }

    // TODO: There's currently no check for duplicates
    // TODO: root folder has isFolder set, so should other files folders
    private function addPath($path, &$vfs) {
        \OCP\Util::writeLog('crate_it', "Crate::addPath(".$path.")", \OCP\Util::DEBUG);
        if(\OC\Files\Filesystem::is_dir($path)) {
            $vfsEntry = $this->addFolderToCrate($path);
        } else {
            $vfsEntry = $this->addFileToCrate($path);
        }
        if($vfsEntry !== NULL) {
            array_push($vfs, $vfsEntry);
        }
    }

    private function addFolderToCrate($folder) {
        $vfsEntry = NULL;
        $name = basename($folder);
        if($name !== '_html') {
            $vfsEntry = array(
                'name' => $name,
                'id' => 'folder', // TODO: change this to 'folder' => true, need to update js
                'children' => array(),
                'folderpath' => $folder
            );
            $vfsContents = &$vfsEntry['children'];
            $paths = \OC\Files\Filesystem::getDirectoryContent($folder);
            foreach($paths as $path) {
                $relativePath = $path->getPath();
                if(Util::startsWith($relativePath, '/'.\OCP\User::getUser().'/files/')) {
                    $relativePath = substr($relativePath, strlen('/'.\OCP\User::getUser().'/files/'));
                }
                $this->addPath($relativePath, $vfsContents);
            }
        }
        return $vfsEntry;
    }


    private function addFileToCrate($file) {
        $fullPath = $this->getFullPath($file);
        $id = md5($fullPath);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fullPath);
        finfo_close($finfo);
        $size = filesize($fullPath);
        $vfsEntry = array(
            'id' => $id,
            'name' => basename($file),
            'filename' => $file,
            'mime' => $mime,
            'size' => $size
        );
        return $vfsEntry;
    }

    public function generateEPUB() {
        \OCP\Util::writeLog('crate_it', "Crate::generateEPUB()", \OCP\Util::DEBUG);
        $files = $this->getPreviewPaths();
        $params = array('files' => $files);
        $epub = Util::renderTemplate('epub', $params);
        $tmpFolder = \OC_Helper::tmpFolder();
        $htmlPath = $tmpFolder.'/'.$this->crateName.'.html';
        file_put_contents($htmlPath, $epub);
        $htmlPath = str_replace(' ', '\ ', $htmlPath);
        $epubPath = $tmpFolder.'/'.$this->crateName.'.epub';
        $command = "ebook-convert '$htmlPath' '$epubPath' --no-default-epub-cover --level1-toc //h:h1 --level2-toc //h:h2 --level3-toc //h:h3 2>&1";
        $command = stripcslashes($command);
        exec($command, $output, $retval);
        if($retval > 0) {
            $message = implode("\n", $output);
            throw new \Exception($message);
        }
        return $epubPath;
    }

    private function getPreviewPaths() {
        $files = $this->flatList();
        $result = array();
        foreach($files as $file) {
            $path = Filesystem::getLocalFile($file['filename']);
            $pathInfo = pathinfo($path);
            $previewPath = Util::joinPaths($pathInfo['dirname'],'_html', $pathInfo['basename'], 'index.html');
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

    private function getAbsolutePath($basename) {
        return Util::joinPaths($this->crateRoot, $basename);
    }

    public function getManifestFileContent() {
        return file_get_contents($this->manifestPath);
    }

}