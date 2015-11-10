<?php

namespace OCP;

class Config {

    public static function getSystemValue($value, $default) {
      return '/var/lib/owncloud/data';
    }

    public static function getUserValue($user, $setting, $email) {
        return '';
    }
    
}