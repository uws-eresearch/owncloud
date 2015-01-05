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

namespace OCA\crate_it\AppInfo;

// use \OCA\AppFramework\App;
// use \OCA\crate_it\DependencyInjection\DIContainer;

$application = new Application();

/*$this->create('crate_it_index', '/')->get()->action(function($params) {
    // call the index method on the class PageController
    App::main('PageController', 'index', $params, new DIContainer());
});*/

/*$application->registerRoutes($this, array(
		'routes' => array(
			array('name' => 'page#index', 'url' => '/', 'verb' => 'GET'),
		)
));*/

$application->registerRoutes($this, array('routes' => array(
		array('name' => 'page#index', 'url' => '/', 'verb' => 'GET'),
		array('name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'),
		array('name' => 'crate#get_items', 'url' => '/crate/get_items', 'verb' => 'GET'),
		array('name' => 'crate#add', 'url' => '/crate/add', 'verb' => 'POST'),
		array('name' => 'crate#get_crate_size', 'url' => '/crate/get_crate_size', 'verb' => 'GET'),
		array('name' => 'crate#update_crate', 'url' => '/crate/update', 'verb' => 'POST'),
		array('name' => 'crate#create_crate', 'url' => '/crate/create', 'verb' => 'POST'),
		array('name' => 'crate#delete_crate', 'url' => '/crate/delete', 'verb' => 'GET'),
		array('name' => 'crate#rename_crate', 'url' => '/crate/rename', 'verb' => 'POST'),
		array('name' => 'crate#package_crate', 'url' => '/crate/downloadzip', 'verb' => 'GET'),
		array('name' => 'crate#readme_preview', 'url' => '/crate/preview', 'verb' => 'GET'),
		array('name' => 'crate#generate_ePUB', 'url' => '/crate/epub', 'verb' => 'GET'),
		array('name' => 'crate_check#check_crate', 'url' => '/crate/check', 'verb' => 'GET'),
		array('name' => 'search#search', 'url' => '/crate/search', 'verb' => 'POST'),
		array('name' => 'publish#publish_crate', 'url' => '/crate/publish', 'verb' => 'POST'),
		array('name' => 'publish#email_receipt', 'url' => '/crate/email', 'verb' => 'POST'),
)));

/*
$this->create('crate_it_get_items', '/crate/get_items')->get()->action(function($params){
    App::main('CrateController', 'getItems', $params, new DIContainer());
});

$this->create('crate_it_add', '/crate/add')->post()->action(function($params) {
    App::main('CrateController', 'add', $params, new DIContainer());
});

$this->create('crate_it_get_crate_size', '/crate/get_crate_size')->get()->action(function($params) {
    App::main('CrateController', 'getCrateSize', $params, new DIContainer());
});

$this->create('crate_it_update', '/crate/update')->post()->action(function($params) {
    App::main('CrateController', 'updateCrate', $params, new DIContainer());
});

$this->create('crate_it_create', '/crate/create')->post()->action(function($params) {
    App::main('CrateController', 'createCrate', $params, new DIContainer());
});

// NOTE: This route should possibly be changed to use the DELETE http method
$this->create('crate_it_delete', '/crate/delete')->get()->action(function($params) {
    App::main('CrateController', 'deleteCrate', $params, new DIContainer());
});

$this->create('crate_it_rename', '/crate/rename')->post()->action(function($params) {
    App::main('CrateController', 'renameCrate', $params, new DIContainer());
});

$this->create('crate_it_search', '/crate/search')->post()->action(function($params) {
    App::main('SearchController', 'search', $params, new DIContainer());
});

$this->create('crate_it_zip', '/crate/downloadzip')->get()->action(function($params) {
    App::main('CrateController', 'packageCrate', $params, new DIContainer());
});

$this->create('crate_it_check', '/crate/check')->get()->action(function($params) {
    App::main('CrateCheckController', 'checkCrate', $params, new DIContainer());
});

$this->create('crate_it_preview', '/crate/preview')->get()->action(function($params) {
    App::main('CrateController', 'readmePreview', $params, new DIContainer());
});

$this->create('crate_it_epub', '/crate/epub')->get()->action(function($params) {
    App::main('CrateController', 'generateEPUB', $params, new DIContainer());
});

$this->create('crate_it_publish', '/crate/publish')->post()->action(function($params) {
    App::main('PublishController', 'publishCrate', $params, new DIContainer());
});

$this->create('crate_it_email_receipt', '/crate/email')->post()->action(function($params) {
    App::main('PublishController', 'emailReceipt', $params, new DIContainer());
});
*/
