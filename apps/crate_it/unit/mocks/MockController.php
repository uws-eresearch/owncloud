<?php

namespace OCP\AppFramework\Controller;

class Controller {

  public $params;

  public function __construct() {}

  public function params($field) {
    return $this->params[$field];
  }

}