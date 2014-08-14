<?php

namespace OCA\crate_it\lib;

use \OCA\AppFramework\Http\DownloadResponse;
/**
 * Prompts the user to download the a textfile.
 */
class ZipDownloadResponse extends DownloadResponse {

    private $zipfilepath;
    private $data;

    public function __construct($zipfilepath, $filename, $contentType='application/zip'){
        parent::__construct($filename, $contentType);
        $this->zipfilepath = $zipfilepath;
        // $this->data = readFile($this->zipfilepath);
    }


    /**
     * Simply sets the headers and returns the file contents
     * @return string the file contents
     */
    public function render(){
        // return $this->data;
        return readFile($this->zipfilepath);
    }
}