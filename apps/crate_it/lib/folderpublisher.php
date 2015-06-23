<?php
/**
 * Created by PhpStorm.
 * User: ilya
 * Date: 21/06/15
 * Time: 1:21 PM
 */

namespace OCA\crate_it\lib;


class FolderPublisher implements Publisher {

    private $endpoint;
    private $collection;

    function __construct($endpoint) {
        $this->endpoint = $endpoint;
        $this->collection = array($this->endpoint['name'] => $this->endpoint['path']);
    }

    public function getCollection() {
        return $this->collection;
    }

    public function publishCrate($package, $collection) {
        \OCP\Util::writeLog('crate_it', "FolderPublisher::publishCrate()", \OCP\Util::DEBUG);
        $basename = basename($package, '.zip');
        $timestamp = Util::getTimestamp();
        $destination = $collection.$basename."_$timestamp.zip";
        \OCP\Util::writeLog('crate_it', "Publishing to $destination", \OCP\Util::DEBUG);
        rename($package, $destination);
        return "{$this->endpoint['url prefix']}$destination";
    }

}