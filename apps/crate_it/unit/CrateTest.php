<?php

require_once 'mocks/MockUtil.php';
require_once 'mocks/MockConfig.php';
require_once 'lib/crate.php';
require_once 'lib/util.php';

use OCA\crate_it\lib\Crate;

class CrateTest extends PHPUnit_Framework_TestCase {
    public function testGetCrateFiles() {
        $crate = new Crate('/tmp', 'Crate A', 'Description for Crate A');
        $this->assertNotNull($crate);
    }

    public function testApplyOverridesWithOverrides() {
        $crate = $this->getMockBuilder('Crate')->setMethods(array('createMetadata'))->getMock();
        $metadata = json_decode('{creators":[{"identifier":"http://dude.org","name":"","email":"","overrides":{"name":"Big Lebowski","email":"dudue@dude.org"}}');
        $crate->method('getManifest')->willReturn($metadata);
        $expected = json_decode('{creators":[{"identifier":"http://dude.org","name":"Big Lebowski","email":"dudue@dude.org","identifier":""}}');
        $actual = $crate->createMetadata();
        $this->assertEquals($expected, $actual);
    }
}

