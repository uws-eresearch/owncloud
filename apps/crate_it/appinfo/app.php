<?php

/**
 * ownCloud - Cr8it App
 *
 * @author Lloyd Harischandra
 * @copyright 2014 University of Western Sydney www.uws.edu.au
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Cr8it;

// dont break owncloud when the appframework is not enabled
if (\OCP\App::isEnabled('appframework')) {
    // Check if the user is logged in
    $api = new \OCA\AppFramework\Core\API('crate_it');

    if ($api -> isLoggedIn()) {
        $user = $api->getUserId();
        \OCP\Util::writeLog('crate_it', "User ".$user." Logged In", 3);
        $api -> addNavigationEntry(array(

        // the string under which your app will be referenced in owncloud
        'id' => $api -> getAppName(),

        // sorting weight for the navigation. The higher the number,
        // the higher will it be listed in the navigation
        'order' => 250,

        // the route that will be shown on startup
        'href' => $api -> linkToRoute("crate_it_index"),

        // the icon that will be shown in the navigation
        "icon" => $api -> imagePath("milk-crate-grey.png"),

        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        "name" => "Cr8It"));
        
        //add 3rdparty folder to include path
        $dir = dirname(dirname(__FILE__)) . '/3rdparty';
        set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
    
        \OC::$CLASSPATH['OCA\crate_it\lib\BagItManager'] = 'crate_it/lib/bagit_manager.php';
        \OC::$CLASSPATH['BagIt'] = 'crate_it/3rdparty/BagIt/bagit.php';
        \OC::$CLASSPATH['BagItManifest'] = 'crate_it/3rdparty/BagIt/bagit_manifest.php';
        \OC::$CLASSPATH['BagItFetch'] = 'crate_it/3rdparty/BagIt/bagit_fetch.php';
    
        // TODO: This dependency needs to be toggleable
        \OC::$CLASSPATH['OCA\file_previewer\lib\Solr'] = 'file_previewer/lib/solr.php';
    
        //load the required files
        \OCP\Util::addscript('crate_it/3rdparty', 'jeditable/jquery.jeditable');
        \OCP\Util::addscript('crate_it/3rdparty', 'jqtree/tree.jquery');
    
        \OCP\Util::addscript('crate_it', 'loader');
        \OCP\Util::addscript('crate_it', 'crate');
    
        // Font awesome
        \OCP\Util::addStyle('crate_it', 'font-awesome');
        \OCP\Util::addStyle('crate_it', 'font-awesome.overrides');
    
        // Bootstrap
        \OCP\Util::addStyle('crate_it/3rdparty', 'bootstrap/bootstrap');
        \OCP\Util::addScript('crate_it/3rdparty', 'bootstrap/bootstrap.min');
        \OCP\Util::addStyle('crate_it', 'bootstrap.overrides');
    
        \OCP\Util::addStyle('crate_it', 'crate');
        \OCP\Util::addStyle('crate_it/3rdparty', 'jqtree/jqtree');
    
        // For tests
        \OCP\Util::addscript('crate_it/3rdparty', 'mockjax/jquery.mockjax');
    
        $config_file = \OC::$SERVERROOT . '/data/cr8it_config.json';
        if (!file_exists($config_file)) {
            $fp = fopen($config_file, 'x');
            $entry = array('max_zip_mb' => 2000, 'max_sword_mb' => 2000, "description_length" => 4000, "previews" => "on");
            fwrite($fp, json_encode($entry));
            fclose($fp);
        }
    }
    

} else {
    $msg = 'Can not enable the Cr8it app because the App Framework ' . 'App is disabled';
    \OCP\Util::writeLog('crate_it', $msg, \OCP\Util::ERROR);
}
