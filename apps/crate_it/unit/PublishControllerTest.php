<?php

require 'MockController.php';
require 'MockJSONResponse.php';
require 'MockUtil.php';
require 'controller/publishcontroller.php';
require 'manager/cratemanager.php';
require 'lib/sword_connector.php';
require 'service/setupservice.php';
require 'service/loggingservice.php';


use OCA\crate_it\Controller\PublishController;
use OCA\AppFramework\Http\JSONResponse;

class PublishControllerTest extends PHPUnit_Framework_TestCase {

    private $publishController;
    private $publisher;

    public function setUp() {
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate', 'getManifestFileContent'))->getMock();
      $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
      $this->publisher = $this->getMockBuilder('OCA\crate_it\lib\SwordConnector')->disableOriginalConstructor()->getMock();
      $loggingService = $this->getMockBuilder('OCA\crate_it\Service\LoggingService')->disableOriginalConstructor()->setMethods(array('log', 'logManifest', 'logPublishedDetails'))->getMock();
      $loggingService->method('log')->willReturn(NULL);
      $loggingService->method('logManifest')->willReturn(NULL);
      $this->publishController = new PublishController(NULL, NULL, $crateManager, $setupService, $this->publisher, $loggingService);
      $this->publishController->params = array('name' => 'test crate', 'endpoint' => 'test server', 'collection' => 'collection 123');
    }


    public function testPublishCrateSuccess() {
      $response = $this->getMockBuilder('SWORDAPPResponse')->setConstructorArgs(array(201, NULL))->getMock();
      $this->publisher->method('publishCrate')->willReturn($response);
      $expected = new JSONResponse(array('msg' => 'Crate \'test crate\' successfully published to collection 123'), 201);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }


    public function testPublishCrateFailure() {
      $response = $this->getMockBuilder('SWORDAPPResponse')->setConstructorArgs(array(400, NULL))->getMock();
      $this->publisher->method('publishCrate')->willReturn($response);
      $expected = new JSONResponse(array('msg' => 'Error: Bad request (400)'), 400);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }


    public function testPublishCrateError() {
      $this->publisher->method('publishCrate')->will($this->throwException(new Exception('Something went wrong')));
      $expected = new JSONResponse(array('msg' => 'Error: Something went wrong'), 500);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }

}