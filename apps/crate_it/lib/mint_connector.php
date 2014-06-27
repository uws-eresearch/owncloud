<?php

namespace OCA\crate_it\lib;

class MintConnector implements SearchProvider {
  
  private $url = NULL;
  private $action = array('activity' => '/Activities/opensearch/lookup?searchTerms=',
                          'FOR', '/ANZSRC_FOR/opensearch/lookup?count=999&level=',
                          'people', '/Parties_People/opensearch/lookup?searchTerms=');

  function __construct() {
    $config = \OCA\crate_it\lib\BagItManager::getConfig();
    $url = $config['url'];
  }
  

  public function search($type, $keyword) {
    $result = array();
    $ch = curl_init();
    $query = $url.$action[$type].urlencode($keyword);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    $result = curl_getinfo($ch);
    curl_close($ch);
    if (!empty($content)) {
      $content_array = json_decode($content);
      $result = $content_array->results;
    }
    return result;
  }



}