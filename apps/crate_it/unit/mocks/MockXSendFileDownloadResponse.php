<?php

namespace OCA\crate_it\lib;

class XSendFileDownloadResponse {

    private $filepath;
    private $filename;

    public function __construct($filepath, $filename){
        $this->$filepath = $filepath;
        $this->$filename = $filename;
    }

}
