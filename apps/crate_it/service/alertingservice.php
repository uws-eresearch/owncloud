<?php

namespace OCA\crate_it\Service;


class AlertingService {

    private $alerters = array();

    public function registerAlerters($alertsConfig) {
        foreach($alertsConfig as $alerter => $endpoints) {
            foreach ($endpoints as $endpoint) {
                if($endpoint['enabled']) {
                    $this->registerAlerter($alerter, $endpoint);
                }
            }
        }
    }

    private function registerAlerter($alerter, $endpoint) {
        $className = 'OCA\crate_it\lib\\'.ucfirst($alerter).'Alerter';
        $this->alerters[$endpoint['name']] = new $className($endpoint);
    }

    public function alert($metadata) {
        foreach($this->alerters as $name => $alerter) {
            $alerter->alert($metadata);
        }
    }

}