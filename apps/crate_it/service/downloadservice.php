<?php

namespace OCA\crate_it\Service;

class DownloadService {
    
    public function prepareZipFile() {
        //$tmp_dir = \OC_Helper::tmpFolder();
        $tmp_dir = '/tmp';
        $zipfile =  $tmp_dir . '/dummy.zip';
         \OCP\Util::writeLog('crate_it', "DownloadService::prepareZipFile() - download file: $zipfile", 3);       
        return $zipfile;
    }
    
    public function prepareEpub() {
        
    }
}