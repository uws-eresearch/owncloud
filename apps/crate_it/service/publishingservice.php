<?php

namespace OCA\crate_it\Service;

use OCA\crate_it\lib\SwordPublisher;

class PublishingService {

    private $endpoints = NULL;

    public function registerEndpoint($endpointsConfig) {
        foreach ($endpointsConfig as $publisher => $endpoints) {
            if ($publisher == 'sword') {
                foreach ($endpoints as $endpoint) {
                    $this->registerSwordEndpoint($endpoint);
                }
            }
        }
    }

    private function registerSwordEndpoint($swordEndpoint) {

    }


}