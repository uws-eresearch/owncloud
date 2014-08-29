<?php

use OCA\crate_it\service\LoggingService;
use OCA\crate_it\manager\CrateManager;
require 'service/loggingservice.php';
require 'MockOC.php';

class LoggingServiceTest extends PHPUnit_Framework_TestCase {

    protected $logger = NULL;
    private $manifest = '{"description":"new crate","creators":[{"source":"manual","id":"1105495651","identifier":"","name":"","email":"","overrides":{"name":"111","email":"1@1.com"}}],"activities":[{"source":"manual","id":"129916321","identifier":"","title":"","date":"","institution":"","grant_number":"","date_submitted":"","description":"","contributors":"","repository_name":"","repository_type":"","oai_set":"","format":"","display_type":"","subject":"","overrides":{"grant_number":"1","date":"1111","title":"1","institution":"1"}}],"vfs":[{"id":"rootfolder","name":"new crate","folder":"true","is_open":"true","children":[{"id":"9c26433778d127fcdb1c5655394f82cd","name":"file.txt","filename":"documents\/file.txt","mime":"text\/plain"}]}]}';
    
    
    protected function setUp() {                       
        shell_exec('mkdir -p unit/data/test');
        $api = $this->getMock('API', array('getUserId'));    
        $api->expects($this->once())
                             ->method('getUserId')
                             ->will($this->returnValue('test'));
                             
        $cm = $this->getMockBuilder('OCA\crate_it\manager\CrateManager')->getMock();                          
        $this->logger = new LoggingService($api, $cm);   
    }
    
    protected function tearDown() {       
       shell_exec('rm -rf unit/data');
    }
    
    public function testLogText() {
        $this->logger->log("test this");
        $result = file_get_contents('unit/data/test/publish.log');
        print $result;
        $this->assertEquals(1, preg_match("/\[*\] $text/", $result));
    }
    
    public function testLogManifest() {                       
        //$this->logger->logManifest('new crate');
        //$result = file_get_contents('unit/data/test/publish.log');
        //print $result;
    }
    
    public function testLogPackageString() {
        $zip = 'unit/testdata/files.zip';
        $this->logger->logPackageStructure($zip);
        $result = file_get_contents('unit/data/test/publish.log');
        print $result;
    }
    
}
    