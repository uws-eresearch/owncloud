<?php

namespace OCA\crate_it\Manager;

class ConfigManager {

    // TODO: Could this be made a static method in a utility class
    public function readConfig() {
        $config = null;
        $config_file = \OC::$SERVERROOT . '/data/cr8it_config.json';
        if (file_exists($config_file)) {
          $config = json_decode(file_get_contents($config_file), true); // convert it to an array.
        }
        return $config;
    }

}