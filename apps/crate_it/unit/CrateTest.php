<?php

require 'lib/crate.php';
use OCA\crate_it\lib\Crate;

class CrateTest extends PHPUnit_Framework_TestCase
{
    public function testGetCrateFiles()
    {       
        $crateRoot = '/tmp';
        
        $crate = new Crate($crateRoot, 'Crate A', 'Description for Crate A');
        $this->assertNotNull($crate);
    }
}
?>
