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
// if (\OCP\App::isEnabled('appframework')) {
    // Check if the user is logged in
    // $api = new \OC\AppFramework\Core\API('crate_it');

    \OCP\App::addNavigationEntry(array(
        // the string under which your app will be referenced in owncloud
        'id' => 'crate_it',
    
        // sorting weight for the navigation. The higher the number,
        // the higher will it be listed in the navigation
        'order' => 250,
    
        // the route that will be shown on startup
        'href' => \OCP\Util::linkToRoute('crate_it.page.index'),
    
        // the icon that will be shown in the navigation
        "icon" => \OCP\Util::imagePath('crate_it', 'milk-crate-grey.png'),
    
        // the title of your application. This will be used in the
        // navigation or on the settings page of your app
        'name' => \OC_L10N::get('crate_it')->t('Cr8It')
        )
    );
    //add project root folder to include path   
    $dir = dirname(dirname(__FILE__)) . '/';
    set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
 
    //add 3rdparty folder to include path   
    $dir = dirname(dirname(__FILE__)) . '/3rdparty';
    set_include_path(get_include_path() . PATH_SEPARATOR . $dir);
           
    //load the required files
    \OCP\Util::addScript('crate_it', 'jquery.jeditable'); 
    \OCP\Util::addScript('crate_it', 'tree.jquery');
    
    \OCP\Util::addScript('crate_it', 'loader');
    \OCP\Util::addScript('crate_it', 'includeme');
    \OCP\Util::addScript('crate_it', 'validation');
    \OCP\Util::addScript('crate_it', 'search');
    \OCP\Util::addScript('crate_it', 'initializers');

    // Font awesome
    \OCP\Util::addStyle('crate_it', 'font-awesome.min');
    \OCP\Util::addStyle('crate_it', 'font-awesome.overrides');

    // Bootstrap
    \OCP\Util::addStyle('crate_it', 'bootstrap');
    \OCP\Util::addScript('crate_it', 'bootstrap.min');
    \OCP\Util::addStyle('crate_it', 'bootstrap.overrides');
    \OCP\Util::addStyle('crate_it', 'crate');
    \OCP\Util::addStyle('crate_it', 'jqtree');

    // TODO: Only load in test environments
    // For tests
    \OCP\Util::addScript('crate_it', 'jquery.mockjax');

// } else {
//     $msg = 'Can not enable the Cr8it app because the App Framework ' . 'App is disabled';
//     \OCP\Util::writeLog('crate_it', $msg, \OCP\Util::ERROR);
// }
