<?php

namespace OCA\crate_it\Service;


use OCA\crate_it\lib\Util;

class LoggingService {

    private $crateManager;
    private $logfile;

    public function __construct($crateManager) {
        $this->crateManager = $crateManager;
        $userPath = Util::getUserPath();
        $this->logfile = Util::joinPaths($userPath, 'publish.log');
    }

    public function log($text) {
        file_put_contents($this->logfile, Util::getTimestamp("[Y-m-d H:i:s P] ").$text."\n", FILE_APPEND);
    }

    // Used for testing
    public function setLog($logfile) {
        $this->logfile = $logfile;
    }

    public function getLog() {
        $contents = file_get_contents($this->logfile);
        if(!$contents) {
            throw new Exception("Unable to write to log file");
        }
        return $contents;
    }

    public function logManifest($crateName) {
        $manifest = $this->crateManager->getManifestFileContent($crateName);
        $text = Util::prettyPrint($manifest);
        $this->log("Manifest JSON for crate '$crateName':");
        $this->log($text);
    }

    public function logPublishedDetails($zip, $crateName) {
        $filesize = filesize($zip);
        $zipname = basename($zip);
        $this->log("Zipped file size for '$zipname': $filesize bytes");
        $this->log("Package content for '$zipname':");
        $this->log("----start content-----");
        $zip = parse_url($zip, PHP_URL_PATH);
        $za = new \ZipArchive();

        $za->open($zip);

        for($i = 0; $i < $za->numFiles; $i++) {
            $stat = $za->statIndex($i);
            if($stat['size'] != 0) {
                $this->log($stat['name']);
            }
            if($stat['name'] == 'manifest-sha1.txt') {
                $sha_content = $za->getFromIndex($i);
            }
        }
        $this->log("----end content-----");
        $checksum = sha1_file($zip);
        $this->log("Checksum (SHA) for $zipname: $checksum");
        $this->log("Content of $crateName's manifest-sha1.txt:");
        $this->log("----start file manifest-sha1.txt-----");
        $this->log("\n".$sha_content);
        $this->log("----end file-----");
    }

}
