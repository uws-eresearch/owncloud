<?php

namespace OCA\crate_it\lib;

require '3rdparty/swordappv2-php-library/swordappclient.php';
use \SWORDAPPClient;

class SwordConnector {
  
  private $swordClient = NULL;
  private static $contentType = 'application/zip';
  private static $packagingFormat = 'http://purl.org/net/sword/package/SimpleZip';
  private $endpoints;

  
  function __construct() {
    $this->swordClient = new SWORDAPPClient();
  }


  public function setEndpoints($endpoints) {
    $this->endpoints = $endpoints;
  }

  private function getServiceDocuments() {
    \OCP\Util::writeLog('crate_it', "SwordConnector::getServiceDocuments()", \OCP\Util::DEBUG);
    $result = array();
    foreach($this->endpoints as $endpoint) {
      if($endpoint['enabled'] && $this->checkAlive($endpoint['sd uri'])) {
        $serviceDocument = $this->swordClient->servicedocument($endpoint['sd uri'], $endpoint['username'], $endpoint['password'], $endpoint['obo']);
        $result[$endpoint['name']] = $serviceDocument;
      }
    }
    return $result;
  }


  public function getCollections() {
    \OCP\Util::writeLog('crate_it', "SwordConnector::getCollections()", \OCP\Util::DEBUG);
    // TODO: Push SD retrieval to constructor?
    $serviceDocuments = $this->getServiceDocuments();
    $result = array();
    foreach($serviceDocuments as $endpoint => $serviceDocument) {
      if($serviceDocument->sac_statusmessage == 'OK') {
        $collections = array();
        foreach($serviceDocument->sac_workspaces as $workspace) {
          foreach($workspace->sac_collections as $collection) {
            $collections["$endpoint: $workspace->sac_workspacetitle - $collection->sac_colltitle"] = $collection->sac_href;
          }
        }
        $result[$endpoint] = $collections;
      } else {
        // TODO: Log error and throw an appropriate exception
      }
    }
    return $result;
  }

  public function publishCrate($package, $endpoint, $collection) {
    \OCP\Util::writeLog('crate_it', "SwordConnector::publishCrate($package, $endpoint, $collection)", \OCP\Util::DEBUG);
    $endpoint = $this->getEndpoint($endpoint);
    return $this->swordClient->deposit($collection, $endpoint['username'], $endpoint['password'], $endpoint['obo'], $package, self::$packagingFormat, self::$contentType, false);
  }

  private function getEndpoint($name) {
    $result = array();
    foreach($this->endpoints as $endpoint) {
      if($endpoint['name'] == $name) {
        $result = $endpoint;
        break;
      }
    }
    return $result;
  }

  private function checkAlive($uri){
    $parsedUrl = parse_url($uri);
    $parsedUrl['port'] = array_key_exists('port', $parsedUrl) ? $parsedUrl['port'] : 80;
    $fsock = fsockopen($parsedUrl['host'], $parsedUrl['port'], $errno, $errstr, 2);
    $result = false;
    if($fsock) {
      $result = true;        
    }
    return $result;
  }

}
