<?php

namespace OCA\crate_it\lib;

class ZipDownloadResponse {

    private $zipfilepath;
    private $filename;

    public function __construct($zipfilepath, $filename){
        $this->zipfilepath = $zipfilepath;
        $this->zipfilepath = $filename;
    }

}