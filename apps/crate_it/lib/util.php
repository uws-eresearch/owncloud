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

    public static function prettyPrint($json) {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen($json);

        for($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if($ends_line_level !== NULL) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if($in_escape) {
                $in_escape = false;
            } else {
                if($char === '"') {
                    $in_quotes = !$in_quotes;
                } else {
                    if(!$in_quotes) {
                        switch($char) {
                            case '}':
                            case ']':
                                $level--;
                                $ends_line_level = NULL;
                                $new_line_level = $level;
                                break;

                            case '{':
                            case '[':
                                $level++;
                            case ',':
                                $ends_line_level = $level;
                                break;

                            case ':':
                                $post = " ";
                                break;

                            case " ":
                            case "\t":
                            case "\n":
                            case "\r":
                                $char = "";
                                $ends_line_level = $new_line_level;
                                $new_line_level = NULL;
                                break;
                        }
                    } else {
                        if($char === '\\') {
                            $in_escape = true;
                        }
                    }
                }
            }
            if($new_line_level !== NULL) {
                $result .= "\n".str_repeat("  ", $new_line_level);
            }
            $result .= $char.$post;
        }
        return $result;
    }
}