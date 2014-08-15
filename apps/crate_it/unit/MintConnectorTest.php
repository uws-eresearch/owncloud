<?php

require 'mock_util.php';

require 'lib/mint_connector.php';
use OCA\crate_it\lib\MintConnector;

require 'lib/curl_wrapper.php';
use OCA\crate_it\lib\CurlWrapper;

class MintConnectorTest extends PHPUnit_Framework_TestCase {

    private $mintConnector = NULL;

    public function testSearchMintSuccess() {
      $url = 'http://intersect.org.au/mint';
      $curlRequest = $this->getMockBuilder('OCA\crate_it\lib\CurlWrapper')->getMock();
      $curlRequest->expects($this->any())->method('setOption');
      $curlRequest->expects($this->once())->method('getStatus')->will($this->returnValue(''));
      $curlRequest->expects($this->once())->method('execute')->will($this->returnValue('{"results":"elvis"}'));
      $curlRequest->expects($this->once())->method('close');
      $this->mintConnector = new MintConnector($url, $curlRequest);
      $expected = 'elvis';
      $actual = $this->mintConnector->search('people', 'elvis');
      $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Grant / Author lookups are not available at this time, try again later. (cURL error: CURLE_HTTP_RETURNED_ERROR)
     */
    public function testSearchMintError() {
      $url = 'http://intersect.org.au/mint';
      $curlRequest = $this->getMockBuilder('OCA\crate_it\lib\CurlWrapper')->getMock();
      $curlRequest->expects($this->any())->method('setOption');
      $curlRequest->expects($this->once())->method('getStatus')->will($this->returnValue('CURLE_HTTP_RETURNED_ERROR'));
      $curlRequest->expects($this->once())->method('execute')->will($this->returnValue(''));
      $curlRequest->expects($this->once())->method('close');
      $this->mintConnector = new MintConnector($url, $curlRequest);
      $this->mintConnector->search('people', 'elvis');
    }
}
