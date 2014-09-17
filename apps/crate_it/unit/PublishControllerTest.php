<?php

require_once 'mocks/MockController.php';
require_once 'mocks/MockJSONResponse.php';
require_once 'mocks/MockUtil.php';
require_once 'controller/publishcontroller.php';
require_once 'manager/cratemanager.php';
require_once 'lib/sword_connector.php';
require_once 'lib/mailer.php';
require_once 'service/setupservice.php';
require_once 'service/loggingservice.php';


use OCA\crate_it\Controller\PublishController;
use OCA\AppFramework\Http\JSONResponse;

class PublishControllerTest extends PHPUnit_Framework_TestCase {

    private $publishController;
    private $publisher;
    private $loggingService;
    private $mailer;

    public function setUp() {
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate', 'getManifestFileContent'))->getMock();
      $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
      $this->publisher = $this->getMockBuilder('OCA\crate_it\lib\SwordConnector')->disableOriginalConstructor()->getMock();
      $this->loggingService = $this->getMockBuilder('OCA\crate_it\Service\LoggingService')->disableOriginalConstructor()->setMethods(array('log', 'logManifest', 'logPublishedDetails', 'getLog'))->getMock();
      $this->loggingService->method('log')->willReturn(NULL);
      $this->loggingService->method('logManifest')->willReturn(NULL);
      $this->mailer = $this->getMockBuilder('OCA\crate_it\lib\Mailer')->setMethods(array('send'))->getMock();
      $this->publishController = new PublishController(NULL, NULL, $crateManager, $setupService, $this->publisher, $this->loggingService, $this->mailer);
      $this->publishController->params = array('name' => 'test crate', 'endpoint' => 'test server', 'collection' => 'collection 123', 'address' => 'test@test.org');
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
      $expected = new JSONResponse(array('msg' => "Error: failed to publish crate 'test crate' to collection 123: Bad request (400)"), 400);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }


    public function testPublishCrateError() {
      $this->publisher->method('publishCrate')->will($this->throwException(new Exception('Something went wrong')));
      $expected = new JSONResponse(array('msg' => "Error: failed to publish crate 'test crate' to collection 123: Something went wrong"), 500);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptSuccess() {
      $_SESSION['last_published_status'] = 'Status';
      $this->mailer->method('send')->willReturn(true);
      $expected = new JSONResponse(array('msg' => 'Publish log sent to test@test.org'), 200);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptFailNoLastPublished() {
      $expected = new JSONResponse(array('msg' => 'Error: No recently published crates'), 500);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptFailMailerError() {
      $_SESSION['last_published_status'] = 'Status';
      $this->mailer->method('send')->willReturn(false);
      $expected = new JSONResponse(array('msg' => 'Error: Unable to send email at this time'), 500);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual); 
    }

    public function testEmailReceiptFailLoggerError() {
      $_SESSION['last_published_status'] = 'Status';
      $this->mailer->method('send')->willReturn(false);
      $this->loggingService->method('getLog')->will($this->throwException(new Exception('Something went wrong')));
      $expected = new JSONResponse(array('msg' => 'Error: Something went wrong'), 500);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual); 
    }
}

