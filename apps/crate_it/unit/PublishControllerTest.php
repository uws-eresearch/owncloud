<?php

require 'MockController.php';
require 'MockJSONResponse.php';
require 'MockUtil.php';
require 'controller/publishcontroller.php';
require 'manager/configmanager.php';
require 'manager/cratemanager.php';
require 'lib/sword_connector.php';


use OCA\crate_it\Controller\PublishController;
use OCA\AppFramework\Http\JSONResponse;

class PublishControllerTest extends PHPUnit_Framework_TestCase {

    public function testPublishCrateSuccess() {
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate'))->getMock();
      $crateManager->method('packageCrate')->willReturn(NULL);
      $publisher = $this->getMockBuilder('OCA\crate_it\lib\SwordConnector')->disableOriginalConstructor()->getMock();
      $response = $this->getMockBuilder('SWORDAPPResponse')->setConstructorArgs(array(201, NULL))->getMock();
      $publisher->method('publishCrate')->willReturn($response);
      $publishController = new PublishController(NULL, NULL, $crateManager, $publisher);
      $publishController->params = array('name' => 'test crate', 'collection' => 'collection 123');
      $expected = new JSONResponse(array('msg' => 'test crate successfully published to collection 123'), 201);
      $actual = $publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }


    public function testPublishCrateFailure() {
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate'))->getMock();
      $crateManager->method('packageCrate')->willReturn(NULL);
      $publisher = $this->getMockBuilder('OCA\crate_it\lib\SwordConnector')->disableOriginalConstructor()->getMock();
      $response = $this->getMockBuilder('SWORDAPPResponse')->setConstructorArgs(array(400, NULL))->getMock();
      $publisher->method('publishCrate')->willReturn($response);
      $publishController = new PublishController(NULL, NULL, $crateManager, $publisher);
      $publishController->params = array('name' => 'test crate', 'collection' => 'collection 123');
      $expected = new JSONResponse(array('msg' => 'Error: Bad request (400)'), 400);
      $actual = $publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }


    public function testPublishCrateError() {
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate'))->getMock();
      $crateManager->method('packageCrate')->willReturn(NULL);
      $publisher = $this->getMockBuilder('OCA\crate_it\lib\SwordConnector')->disableOriginalConstructor()->getMock();
      $publisher->method('publishCrate')->will($this->throwException(new Exception('Something went wrong')));
      $publishController = new PublishController(NULL, NULL, $crateManager, $publisher);
      $publishController->params = array('name' => 'test crate', 'collection' => 'collection 123');
      $expected = new JSONResponse(array('msg' => 'Error: Something went wrong'), 500);
      $actual = $publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }

}