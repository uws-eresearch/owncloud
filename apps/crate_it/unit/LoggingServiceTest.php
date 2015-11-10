<?php
 
require_once 'mocks/MockOC.php';
require_once 'mocks/MockUser.php';
require_once 'mocks/MockConfig.php';

require_once 'service/loggingservice.php';
use OCA\crate_it\service\LoggingService;
require_once 'manager/cratemanager.php';
use OCA\crate_it\manager\CrateManager;


class LoggingServiceTest extends PHPUnit_Framework_TestCase {

    protected $logger = NULL;
    private $manifest = '{"description":"new crate","creators":[{"source":"manual","id":"1105495651","identifier":"","name":"","email":"","overrides":{"name":"111","email":"1@1.com"}}],"activities":[{"source":"manual","id":"129916321","identifier":"","title":"","date":"","institution":"","grant_number":"","date_submitted":"","description":"","contributors":"","repository_name":"","repository_type":"","oai_set":"","format":"","display_type":"","subject":"","overrides":{"grant_number":"1","date":"1111","title":"1","institution":"1"}}],"vfs":[{"id":"rootfolder","name":"new crate","folder":"true","is_open":"true","children":[{"id":"9c26433778d127fcdb1c5655394f82cd","name":"file.txt","filename":"documents\/file.txt","mime":"text\/plain"}]}]}';
    private $logPath = 'unit/data/test/publish.log';
    
    
    protected function setUp() {                       
        shell_exec('mkdir -p unit/data/test');
        $crateManager = $this->getMock('CrateManager', array('getManifestFileContent'));
        $crateManager->expects($this->any())->method('getManifestFileContent')->will($this->returnValue($this->manifest));
        $this->logger = new LoggingService($crateManager);
        $this->logger->setLog($this->logPath);
    }
    
    protected function tearDown() {       
       shell_exec('rm -rf unit/data');
    }
    
    public function testLogText() {
        $text = "test this";
        $this->logger->log($text);
        $result = file_get_contents($this->logPath);
        $this->assertEquals(1, preg_match("/\[*\] $text/", $result));
    }
    
    public function testLogManifest() {
        $this->markTestSkipped('must be revisited.');
        $text = $this->manifest;                       
        $this->logger->logManifest('new crate');
        $result = file_get_contents($this->logPath);
        $this->assertEquals(1, preg_match("/\[*\] $text/", $result));
    }
    
    public function testLogPublishedDetails() {
        $this->markTestSkipped('must be revisited.');
        $zip = 'unit/testdata/files.zip';
        $this->logger->logPublishedDetails($zip, "new crate");
    }
    
}
    