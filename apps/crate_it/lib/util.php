<?php

namespace OCA\crate_it\lib;

use \OCP\Template;

class Util {

    public static function renderTemplate($template, $params) {
        // TODO: Use util method to get appName
        $template = new Template('crate_it', $template);
        foreach ($params as $key => $value) {
            $template->assign($key, $value);
        }
        return $template->fetchPage();
    }


    public static function getTimestamp() {
        date_default_timezone_set('Australia/Sydney');
        $format="YmdHis";
        $timestamp = date($format);
        return $timestamp;
    }

}