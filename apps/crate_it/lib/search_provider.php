<?php

namespace OCA\crate_it\lib;

interface SearchProvider {
  
  public function search($type, $keywords);

}