<?php

namespace OCA\crate_it\lib;
require 'swordappv2-php-library/swordappclient.php';
use \SWORDAPPClient;

class SwordConnector {
  
  private $swordClient = NULL;
  private $username = NULL;
  private $password = NULL;
  private $sdUri = NULL;
  private $obo = NULL;

  
  function __construct($username, $password, $sdUri, $obo) {
    $this->swordClient = new SWORDAPPClient();
    $this->username = $username;
    $this->password = $password;
    $this->sdUri = $sdUri;
    $this->obo = $obo;
  }
  
  private function getServiceDocument() {
    \OCP\Util::writeLog('crate_it', "SwordConnector::getServiceDocument()", \OCP\Util::DEBUG);
    return $this->swordClient->servicedocument($this->sdUri, $this->username, $this->password, $this->obo);
  }


  public function getCollections() {
    \OCP\Util::writeLog('crate_it', "SwordConnector::getCollections()", \OCP\Util::DEBUG);
    $serviceDocument = $this->getServiceDocument();
    $result = array();
    if($serviceDocument->sac_statusmessage == 'OK') {
      foreach($serviceDocument->sac_workspaces as $workspace) {
        foreach($workspace->sac_collections as $collection) {
          $result["$workspace->sac_workspacetitle - $collection->sac_colltitle"] = $collection->sac_href;
        }
      }
    } else {
      // TODO: Log error and throw an appropriate exception
    }
    return $result;
  }


}