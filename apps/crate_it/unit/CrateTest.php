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
}

