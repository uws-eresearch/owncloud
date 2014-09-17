<?php

namespace OCA\AppFramework\Http;

class TextResponse {
  
  public $message;
  private $status = 200;

  public function __construct($message) {
    $this->message = $message;
  }

  public function setStatus($status) {
    $this->status = $status;
  }

  public function getStatus() {
    return $this->status;
  }

}