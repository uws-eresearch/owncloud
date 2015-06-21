<?php

namespace OCA\crate_it\Service;

use OCA\crate_it\lib\SwordPublisher;
use OCA\crate_it\lib\FolderPublisher;

class PublishingService {

    private $publishers = array();

    public function registerPublishers($endpointsConfig) {
        foreach($endpointsConfig as $publisher => $endpoints) {
            if($publisher == 'sword') {
                foreach ($endpoints as $endpoint) {
                    if($endpoint['enabled']) {
                        $this->registerSwordPublisher($endpoint);
                    }
                }
            }
            if($publisher == 'folder') {
                foreach ($endpoints as $endpoint) {
                    if($endpoint['enabled']) {
                        $this->registerFolderPublisher($endpoint);
                    }
                }
            }
        }
    }

    // TODO: lot of duplication here and in registerPublishers
    private function registerSwordPublisher($swordEndpoint) {
        $this->publishers[$swordEndpoint['name']] = new SwordPublisher($swordEndpoint);
    }

    private function registerFolderPublisher($folderEndpoint) {
        $this->publishers[$folderEndpoint['name']] = new FolderPublisher($folderEndpoint);
    }

    public function getCollections() {
        $result = array();
        foreach($this->publishers as $name => $publisher) {
            $collections = $publisher->getCollection();
            $result[$name] = $collections;
        }
        return $result;
    }

    public function publishCrate($crate, $publisher, $collection) {
        $this->publishers[$publisher]->publishCrate($crate, $collection);
    }

}