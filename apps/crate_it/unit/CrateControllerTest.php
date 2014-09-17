<?php

require_once 'mocks/MockController.php';
require_once 'mocks/MockZipDownloadResponse.php';
require_once 'mocks/MockTextResponse.php';
require_once 'mocks/MockUtil.php';
require_once 'mocks/MockHttp.php';
require_once 'service/setupservice.php';
require_once 'service/crateservice.php';
require_once 'controller/cratecontroller.php';

use \OCA\crate_it\Controller\CrateController;
use OCA\AppFramework\Http;
use OCA\AppFramework\Http\JSONResponse;
use OCA\AppFramework\Http\TextResponse;
use OCA\crate_it\lib\ZipDownloadResponse;

class CrateControllerTest extends PHPUnit_Framework_TestCase {

  private $crateController;
  private $crateService;

  public function setUp() {
    $this->crateService = $crateService = $this->getMockBuilder('OCA\crate_it\Service\CrateService')->disableOriginalConstructor()->setMethods(array('generateEPUB', 'packageCrate'))->getMock();
    $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
    $this->crateController = new CrateController(NULL, NULL, $crateService, $setupService);
  }

  public function testGenerateEPUBSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateService->method('generateEPUB')->willReturn('/tmp/test.epub');
    $expected = new ZipDownloadResponse('/tmp/test.epub', 'test.epub');
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($expected, $actual);
  }

  public function testGenerateEPUBFailure() {
    $_SESSION['selected_crate'] = 'test';
    $message = 'Server has caught on fire';
    $this->crateService->method('generateEPUB')->will($this->throwException(new Exception($message)));
    $expected = new TextResponse("Internal Server Error: $message");
    $expected->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($expected, $actual);
  }

  public function testPackageCrateSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateService->method('packageCrate')->willReturn('/tmp/test.zip');
    $expected = new ZipDownloadResponse('/tmp/test.zip', 'test.zip');
    $actual = $this->crateController->packageCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testPackageCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $message = 'Server has caught on fire';
    $this->crateService->method('packageCrate')->will($this->throwException(new Exception($message)));
    $expected = new TextResponse("Internal Server Error: $message");
    $expected->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
    $actual = $this->crateController->packageCrate();
    $this->assertEquals($expected, $actual);
  }

}