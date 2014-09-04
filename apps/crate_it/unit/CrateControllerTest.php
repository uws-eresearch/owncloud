<?php

require 'MockController.php';
require 'MockZipDownloadResponse.php';
require 'service/setupservice.php';
require 'service/crateservice.php';
require 'controller/cratecontroller.php';

use OCA\AppFramework\Http\JSONResponse;
use \OCA\crate_it\Controller\CrateController;

class CrateControllerTest extends PHPUnit_Framework_TestCase {

  private $crateController;
  private $crateService;

  public function setUp() {
    $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate'))->getMock();
    $this->crateService = $crateService = $this->getMockBuilder('OCA\crate_it\Service\CrateService')->disableOriginalConstructor()->getMock();
    $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
    $this->crateController = new CrateController(NULL, NULL, $crateService, $setupService);
  }

  public function testGenerateEPUBSuccess() {

  }



}