<?php

namespace OCA\AppFramework\Http;

class JSONResponse {
  
  public $data;
  public $status;

  public function __construct($data, $status) {
    $this->data = $data;
    $this->status = $status;
  }

}