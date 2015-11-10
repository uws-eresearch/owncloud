<?php

namespace OCA\crate_it\lib;
// TODO: find a cleaner way to import
require __DIR__ . '/../3rdparty/swordappv2-php-library/swordappclient.php';
use SWORDAPPClient;
use WebDriver\Exception;

class SwordPublisher implements Publisher {

    private $swordClient;
    private static $contentType = 'application/zip';
    private static $packagingFormat = 'http://purl.org/net/sword/package/SimpleZip';
    private $endpoint;
    private $serviceDocument;

    function __construct($endpoint) {
        $this->swordClient = new SWORDAPPClient();
        $this->endpoint = $endpoint;
    }

    private function getServiceDocument() {
        \OCP\Util::writeLog('crate_it', "SwordPublisher::getServiceDocuments()", \OCP\Util::DEBUG);
        if ($this->checkAlive($this->endpoint['sd uri'])) {
            $result = $this->swordClient->servicedocument($this->endpoint['sd uri'], $this->endpoint['username'],
                $this->endpoint['password'], $this->endpoint['obo']);
        }
        return $result;
    }


    public function getCollection() {
        \OCP\Util::writeLog('crate_it', "SwordPublisher::getCollection()", \OCP\Util::DEBUG);
        // TODO: Push SD retrieval to constructor?
        if($this->serviceDocument === NULL) {
            $this->serviceDocument = $this->getServiceDocument();
        }
        $result = array();
        if ($this->serviceDocument->sac_statusmessage == 'OK') {
            foreach ($this->serviceDocument->sac_workspaces as $workspace) {
                foreach ($workspace->sac_collections as $collection) {
                    $result["$workspace->sac_workspacetitle - $collection->sac_colltitle"] = $collection->sac_href;
                }
            }
        } else {
            // TODO: Log error and throw an appropriate exception
        }
        return $result;
    }

    public function publishCrate($package, $collection) {
        \OCP\Util::writeLog('crate_it', "SwordPublisher::publishCrate($package, $this->endpoint, $collection)", \OCP\Util::DEBUG);
        $response = $this->swordClient->deposit($collection, $this->endpoint['username'], $this->endpoint['password'],
            $this->endpoint['obo'], $package, self::$packagingFormat, self::$contentType, false);
        if ($response->sac_status != 201) {
            throw \Exception("Error: failed to publish crate '".basname($package)."' to $collection: ");
        }
        // TODO: return that actual deposited item URL,
        return $collection;
    }

    private function checkAlive($uri) {
        $parsedUrl = parse_url($uri);
        $parsedUrl['port'] = array_key_exists('port', $parsedUrl) ? $parsedUrl['port'] : 80;
        $fsock = fsockopen($parsedUrl['host'], $parsedUrl['port'], $errno, $errstr, 2);
        $result = false;
        if ($fsock) {
            $result = true;
        }
        return $result;
    }

}
