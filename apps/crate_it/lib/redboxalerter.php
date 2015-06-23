<?php

namespace OCA\crate_it\lib;


class RedboxAlerter implements Alerter {

    private $endpoint;

    public function __construct($endpoint) {
        $this->endpoint = $endpoint;
    }

    public function alert($metadata) {
        $xml = Util::renderTemplate('xml', $metadata);
        $timestamp = Util::getTimestamp();
        $filePath = $this->endpoint['path']."{$timestamp}_{$metadata['crate_name']}.xml";
        file_put_contents($filePath, $xml);
    }
}