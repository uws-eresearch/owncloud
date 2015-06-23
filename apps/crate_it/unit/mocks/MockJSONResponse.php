<?php

namespace OCP\AppFramework\Http;

class JSONResponse {
  
  public $data;
  public $status;

  public function __construct($data, $status=200) {
    $this->data = $data;
    $this->status = $status;
  }

}