<?php

require_once 'mocks/MockController.php';
require_once 'mocks/MockZipDownloadResponse.php';
require_once 'mocks/MockTextResponse.php';
require_once 'mocks/MockJSONResponse.php';
require_once 'mocks/MockUtil.php';
require_once 'mocks/MockHttp.php';
require_once 'service/setupservice.php';
require_once 'service/crateservice.php';
require_once 'controller/cratecontroller.php';

use \OCA\crate_it\Controller\CrateController;
use OCP\AppFramework\Http;
use OCA\AppFramework\Http\JSONResponse;
use OCA\AppFramework\Http\TextResponse;
use OCA\crate_it\lib\ZipDownloadResponse;

class CrateControllerTest extends PHPUnit_Framework_TestCase {

  private $crateController;
  private $crateService;
  private $crateServiceMethods = array('generateEPUB', 'packageCrate', 'createCrate', 'getItems');
  private $exception;
  private $textErrorResponse;
  private $jsonErrorResponse;

  public function setUp() {
    $errorMessage = 'Server has caught on fire';
    $this->exception = new Exception($errorMessage);
    $this->jsonErrorResponse = new JSONResponse(array('msg' => $errorMessage), Http::STATUS_INTERNAL_SERVER_ERROR);
    $this->textErrorResponse = new TextResponse("Internal Server Error: $errorMessage");
    $this->textErrorResponse->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
    $this->crateService = $crateService = $this->getMockBuilder('OCA\crate_it\Service\CrateService')->disableOriginalConstructor()->setMethods($this->crateServiceMethods)->getMock();
    $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
    $this->crateController = new CrateController(NULL, NULL, $crateService, $setupService);
  }

  public function testCreateCrateSuccess() {
    $crateName = 'test';
    $_SESSION['selected_crate'] = $crateName;
    $this->crateService->method('createCrate')->willReturn($crateName);
    $expected = new JSONResponse(array('crateName' => $crateName, 'crateDescription' => ''));
    $actual = $this->crateController->createCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testCreateCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateService->method('createCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->createCrate();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testGetItemsSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $manifestData = array('files' => 'test');
    $this->crateService->method('getItems')->willReturn($manifestData);
    $expected = new JSONResponse($manifestData);
    $actual = $this->crateController->getItems();
    $this->assertEquals($expected, $actual);
  }

  public function testGetItemsFailure() {
    $_SESSION['selected_crate'] = 'test';
    $message = 'Server has caught on fire';
    $this->crateService->method('getItems')->will($this->throwException($this->exception));
    $actual = $this->crateController->getItems();
    $this->assertEquals($this->jsonErrorResponse, $actual);
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
    $this->crateService->method('generateEPUB')->will($this->throwException($this->exception));
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($this->textErrorResponse, $actual);
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
    $this->crateService->method('packageCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->packageCrate();
    $this->assertEquals($this->textErrorResponse, $actual);
  }

}