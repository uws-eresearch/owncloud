<?php

namespace OCA\crate_it\lib;

require '3rdparty/swordappv2-php-library/swordappclient.php';
use \SWORDAPPClient;

class SwordConnector {
  
  private $swordClient = NULL;
  private $username = NULL;
  private $password = NULL;
  private $sdUri = NULL;
  private $obo = NULL;
  private static $contentType = 'application/zip';
  private static $packagingFormat = 'http://purl.org/net/sword/package/SimpleZip';

  
  function __construct($configManager) {
    $config = $configManager->readConfig();
    $sword = $config['sword'];
    $this->username = $sword['username'];
    $this->password = $sword['password'];
    $this->sdUri = $sword['sd_uri'];
    $this->obo = $sword['obo'];
    $this->swordClient = new SWORDAPPClient();
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


  public function publishCrate($package, $collection) {
    \OCP\Util::writeLog('crate_it', "SwordConnector::publishCrate($package, $collection)", \OCP\Util::DEBUG);
    return $this->swordClient->deposit($collection, $this->username, $this->password, $this->obo, $package, self::$packagingFormat, self::$contentType, false);
  }

}
