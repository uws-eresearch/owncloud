<?php

require_once 'mocks/MockController.php';
require_once 'mocks/MockZipDownloadResponse.php';
require_once 'mocks/MockXSendFileDownloadResponse.php';
require_once 'mocks/MockTextResponse.php';
require_once 'mocks/MockJSONResponse.php';
require_once 'mocks/MockUtil.php';
require_once 'mocks/MockHttp.php';
require_once 'mocks/MockUser.php';
require_once 'mocks/MockConfig.php';
require_once 'mocks/MockOC.php';
require_once 'service/setupservice.php';
require_once 'manager/cratemanager.php';
require_once 'controller/cratecontroller.php';

use \OCA\crate_it\Controller\CrateController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TextResponse;
use OCA\crate_it\lib\ZipDownloadResponse;
use OCA\crate_it\lib\XSendFileDownloadResponse;

class CrateControllerTest extends PHPUnit_Framework_TestCase {

  private $crateController;
  private $crateManager;
  private $crateManagerMethods = array('generateEPUB', 'packageCrate', 'createCrate', 'getManifest', 'addToCrate', 'getCrateSize', 'updateCrate', 'renameCrate');
  private $exception;
  private $textErrorResponse;
  private $jsonErrorResponse;

  public function setUp() {
    $errorMessage = 'Server has caught on fire';
    $this->exception = new Exception($errorMessage);
    $this->jsonErrorResponse = new JSONResponse(array('msg' => $errorMessage), Http::STATUS_INTERNAL_SERVER_ERROR);
    $this->textErrorResponse = new TextResponse("Internal Server Error: $errorMessage");
    $this->textErrorResponse->setStatus(Http::STATUS_INTERNAL_SERVER_ERROR);
    $this->crateManager = $crateManager = $this->getMockBuilder('OCA\crate_it\manager\crateManager')->disableOriginalConstructor()->setMethods($this->crateManagerMethods)->getMock();
    $this->crateController = new CrateController(NULL, NULL, $crateManager);
  }

  public function testCreateCrateSuccess() {
    $crateName = 'test';
    $_SESSION['selected_crate'] = $crateName;
    $this->crateManager->method('createCrate')->willReturn($crateName);
    $expected = new JSONResponse(array('crateName' => $crateName, 'crateDescription' => '', 'crateDataRetentionPeriod' => 'Perpetuity'));
    $actual = $this->crateController->createCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testCreateCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('createCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->createCrate();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testgetManifestSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $manifestData = array('files' => 'test');
    $this->crateManager->method('getManifest')->willReturn($manifestData);
    $expected = new JSONResponse($manifestData);
    $actual = $this->crateController->getManifest();
    $this->assertEquals($expected, $actual);
  }

  public function testgetManifestFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('getManifest')->will($this->throwException($this->exception));
    $actual = $this->crateController->getManifest();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testAddToCrateSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $expected = new JSONResponse(array('msg' => ' added to crate test'));
    $actual = $this->crateController->add();
    $this->assertEquals($expected, $actual);
  }

  public function testAddToCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('addToCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->add();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testGetCrateSizeSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $content = array('size' => 20, 'human' => '20 bytes');
    $this->crateManager->method('getCrateSize')->willReturn($content);
    $expected = new JSONResponse($content);
    $actual = $this->crateController->getCrateSize();
    $this->assertEquals($expected, $actual);
  }

  public function testGetCrateSizeFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('getCrateSize')->will($this->throwException($this->exception));
    $actual = $this->crateController->getCrateSize();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testUpdateCrateSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $expected = new JSONResponse(array('msg' => ' successfully updated', 'value' => NULL));
    $actual = $this->crateController->updateCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testUpdateCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('updateCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->updateCrate();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }

  public function testRenameCrateSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $expected = new JSONResponse(array('msg' => 'Renamed test to '));
    $actual = $this->crateController->renameCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testRenameCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('renameCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->renameCrate();
    $this->assertEquals($this->jsonErrorResponse, $actual);
  }


  public function testGenerateEPUBSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('generateEPUB')->willReturn('/tmp/test.epub');
    $expected = new ZipDownloadResponse('/tmp/test.epub', 'test.epub');
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($expected, $actual);
  }

  public function testGenerateEPUBWithSpacesSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('generateEPUB')->willReturn('/tmp/test with spaces.epub');
    $expected = new ZipDownloadResponse('/tmp/test with spaces.epub', 'test with spaces.epub');
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($expected, $actual);
  }

  public function testGenerateEPUBFailure() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('generateEPUB')->will($this->throwException($this->exception));
    $actual = $this->crateController->generateEPUB();
    $this->assertEquals($this->textErrorResponse, $actual);
  }

  public function testPackageCrateSuccess() {
    $_SESSION['selected_crate'] = 'test';
    $this->crateManager->method('packageCrate')->willReturn('/tmp/test.zip');
    $expected = new XSendFileDownloadResponse('/tmp/test.zip', 'test.zip');
    $actual = $this->crateController->packageCrate();
    $this->assertEquals($expected, $actual);
  }

  public function testPackageCrateFailure() {
    $_SESSION['selected_crate'] = 'test';
    $message = 'Server has caught on fire';
    $this->crateManager->method('packageCrate')->will($this->throwException($this->exception));
    $actual = $this->crateController->packageCrate();
    $this->assertEquals($this->textErrorResponse, $actual);
  }

}