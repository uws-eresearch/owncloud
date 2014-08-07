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

    $api->addNavigationEntry(array(
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
        "name" => $api->getTrans()->t("Cr8It")
        )
    );
    //add 3rdparty folder to include path   
    $dir = dirname(dirname(__FILE__)) . '/3rdparty';
    set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
        
    //load the required files
    $api->add3rdPartyScript('jeditable/jquery.jeditable');
    $api->add3rdPartyScript('jqtree/tree.jquery');


    $api->addScript('loader');
    $api->addScript('includeme');
    $api->addScript('util');
    $api->addScript('search');
    $api->addScript('initializers');

    // Font awesome
    $api->addStyle('font-awesome');
    $api->addStyle('font-awesome.overrides');

    // Bootstrap
    $api->add3rdPartyStyle('bootstrap/bootstrap');
    $api->add3rdPartyScript('bootstrap/bootstrap.min');
    $api->addStyle('bootstrap.overrides');

    $api->addStyle('crate');
    $api->add3rdPartyStyle('jqtree/jqtree');

    // For tests
    $api->add3rdPartyScript('mockjax/jquery.mockjax');

} else {
    $msg = 'Can not enable the Cr8it app because the App Framework ' . 'App is disabled';
    \OCP\Util::writeLog('crate_it', $msg, \OCP\Util::ERROR);
}
