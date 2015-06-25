<?php

namespace OCA\crate_it\lib;

use OCP\Template;

class Util {

    public static function renderTemplate($template, $params) {
        // TODO: Use util method to get appName
        $template = new Template('crate_it', $template);
        foreach($params as $key => $value) {
            $template->assign($key, $value);
        }
        return $template->fetchPage();
    }


    public static function getTimestamp($format="YmdHis") {
        date_default_timezone_set('Australia/Sydney');
        $timestamp = date($format);
        return $timestamp;
    }

    public static function getConfig() {
        $configFile = Util::joinPaths(Util::getDataPath(),'cr8it_config.json');
        $config = NULL; // Allows tests to work
        // TODO: Throw a better error when there is invalid json or the config is not found
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
        }
        return $config;
    }

    public static function getDataPath() {
        return \OCP\Config::getSystemValue('datadirectory', \OC::$SERVERROOT.'/data');
    }

    public static function getUserPath() {
        $userId = \OCP\User::getUser();
        $config = Util::getConfig();
        return Util::joinPaths($config['crate path'], $userId);
    }

    public static function joinPaths() {
        $paths = array();
        foreach(func_get_args() as $arg) {
            if($arg !== '') {
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', join('/', $paths));
    }
}