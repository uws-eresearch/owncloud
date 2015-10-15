<?php

require_once 'mocks/MockController.php';
require_once 'mocks/MockJSONResponse.php';
require_once 'mocks/MockUtil.php';
require_once 'mocks/MockTemplate.php';
require_once 'controller/publishcontroller.php';
require_once 'manager/cratemanager.php';
require_once 'lib/publisher.php';
require_once 'lib/mailer.php';
require_once 'service/setupservice.php';
require_once 'service/loggingservice.php';
require_once 'service/publishingservice.php';

use OCA\crate_it\Controller\PublishController;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Template;
use OCA\crate_it\lib\Util;

class PublishControllerTest extends PHPUnit_Framework_TestCase {

    private $publishController;
    private $publishingService;
    private $loggingService;
    private $mailer;

    public function setUp() {
      $alertingService = $this->getMockBuilder('OCA\crate_it\Service\AlertingService')->disableOriginalConstructor()->setMethods(array('registerAlerters', 'alert'))->getMock();
      $this->publishingService = $this->getMockBuilder('OCA\crate_it\Service\PublishingService')->disableOriginalConstructor()->setMethods(array('registerPublishers', 'publishCrate'))->getMock();
      $crateManager = $this->getMockBuilder('OCA\crate_it\Manager')->disableOriginalConstructor()->setMethods(array('packageCrate', 'getManifestFileContent', 'createMetadata'))->getMock();
      $setupService = $this->getMockBuilder('OCA\crate_it\Service\SetupService')->disableOriginalConstructor()->setMethods(array('getParams'))->getMock();
      $this->loggingService = $this->getMockBuilder('OCA\crate_it\Service\LoggingService')->disableOriginalConstructor()->setMethods(array('log', 'logManifest', 'logPublishedDetails', 'getLog'))->getMock();
      $this->loggingService->method('log')->willReturn(NULL);
      $this->loggingService->method('logManifest')->willReturn(NULL);
      $this->mailer = $this->getMockBuilder('OCA\crate_it\lib\Mailer')->setMethods(array('sendHtml'))->getMock();
      $this->publishController = new PublishController(NULL, NULL, $crateManager, $setupService, $this->publishingService, $alertingService, $this->loggingService, $this->mailer);
      $manifest = '{"description":"","data_retention_period":"25","submitter":{"email":"test@test.org","displayname":"test"},"creators":[],"activities":[],"vfs":[{"id":"rootfolder","name":"test_crate1","folder":true,"valid":"true","is_open":true}]}';
      $metadata = json_decode($manifest, true);
      $this->publishController->params = array('name' => 'test crate', 'endpoint' => 'test server', 'collection' => 'collection 123', 'address' => 'test@test.org', 'metadata' => $metadata);
    }


    public function testPublishCrateSuccess() {
      $this->publishingService->method('publishCrate')->willReturn('path');
      $metadata = array('location'=>'path', 'url' => 'http://www.intersect.org.au/path', 'submitted_date'=> Util::getTimestamp("Y-m-d"), 'submitted_time' => Util::getTimestamp("H:i:s"));
      $expected = new JSONResponse(array('msg' => 'Crate \'test crate\' successfully submitted.', 'metadata'=> $metadata), 201);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }

    public function testPublishCrateFailure() {
      $this->publishingService->method('publishCrate')->will($this->throwException(new Exception('Something went wrong')));
      $expected = new JSONResponse(array('msg' => "Error: failed to submit crate 'test crate': Something went wrong"), 500);
      $actual = $this->publishController->publishCrate();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptSuccess() {
      $_SESSION['last_published_status'] = 'Status';
      $this->mailer->method('sendHtml')->willReturn(true);
      $expected = new JSONResponse(array('msg' => 'A confirmation email has been sent to test@test.org'), 200);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptFailNoLastPublished() {
      $expected = new JSONResponse(array('msg' => 'Error: No recently submitted crates'), 500);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual);
    }

    public function testEmailReceiptFailMailerError() {
      $_SESSION['last_published_status'] = 'Status';
      $this->mailer->method('sendHtml')->willReturn(false);
      $expected = new JSONResponse(array('msg' => 'Error: Unable to send email at this time'), 500);
      $actual = $this->publishController->emailReceipt();
      $this->assertEquals($expected, $actual); 
    }

}

