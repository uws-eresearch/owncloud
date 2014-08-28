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
      if($endpoint['enabled']) {
        $serviceDocument = $this->swordClient->servicedocument($endpoint['sd uri'], $endpoint['username'], $endpoint['password'], $endpoint['obo']);
        $result[$endpoint['name']] = $serviceDocument;
      }
    }
    return $result;
  }


  public function getCollections() {
    \OCP\Util::writeLog('crate_it', "SwordConnector::getCollections()", \OCP\Util::DEBUG);
    // TODO: Push SD retrieval to constructor
    $serviceDocuments = $this->getServiceDocuments();
    $result = array();
    foreach($serviceDocuments as $endpoint => $serviceDocument) {
      if($serviceDocument->sac_statusmessage == 'OK') {
        foreach($serviceDocument->sac_workspaces as $workspace) {
          foreach($workspace->sac_collections as $collection) {
            $result["$endpoint: $workspace->sac_workspacetitle - $collection->sac_colltitle"] = $collection->sac_href;
          }
        }
      } else {
        // TODO: Log error and throw an appropriate exception
      }
    }
    // var_dump($result);
    return $result;
  }

  public function publishCrate($package, $collection) {
    \OCP\Util::writeLog('crate_it', "SwordConnector::publishCrate($package, $collection)", \OCP\Util::DEBUG);
    return $this->swordClient->deposit($collection, $this->username, $this->password, $this->obo, $package, self::$packagingFormat, self::$contentType, false);
  }

}
