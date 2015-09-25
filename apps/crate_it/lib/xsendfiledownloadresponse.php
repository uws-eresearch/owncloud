<?php

namespace OCA\crate_it\lib;

use \OCP\AppFramework\Http\DownloadResponse;
use OCA\crate_it\lib\Util;

/**
 * Prompts the user to download the provided file.
 * Assumes that XSendFile is installed on Apache, and configured to serve files accordingly.
 * See https://tn123.org/mod_xsendfile/
 */
class XSendFileDownloadResponse extends DownloadResponse {

    public function __construct($filepath, $filename, $contentType='application/octet-stream') {
        parent::__construct($filename, $contentType);
        $this->addHeader('X-Sendfile', $filepath);
    }
}