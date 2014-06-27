<?php

namespace OCA\crate_it\lib;
require 'search_provider.php';

class MintConnector implements SearchProvider {
  
  private $url = NULL;
  private $action = array('activity' => '/Activities/opensearch/lookup?searchTerms=',
                          'FOR' => '/ANZSRC_FOR/opensearch/lookup?count=999&level=',
                          'people' => '/Parties_People/opensearch/lookup?searchTerms=');

  function __construct() {
    $config = \OCA\crate_it\lib\BagItManager::getConfig();
    $this->url = $config['mint']['url'];
  }
  

  public function search($type, $keywords) {
    $result = array();
    // try {
      $ch = curl_init();
      $query = $this->url.$this->action[$type].urlencode($keywords);
      \OCP\Util::writeLog("crate_it::search", $query, \OCP\Util::DEBUG);
      curl_setopt($ch, CURLOPT_URL, $query);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $content = curl_exec($ch);
      // $result = curl_getinfo($ch);
      curl_close($ch);
      if (!empty($content)) {
        $content_array = json_decode($content);
        $result = $content_array->results;
      }
    // } catch(Exception $e) {
    //   header('HTTP/1.1 400 ' . $e->getMessage());
    // }
    return $result;
  }



}