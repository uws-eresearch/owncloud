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

namespace OCA\crate_it;

use \OCA\AppFramework\App;
use \OCA\crate_it\DependencyInjection\DIContainer;


$this->create('crate_it_index', '/')->get()->action(function($params) {
    // call the index method on the class PageController
    App::main('PageController', 'index', $params, new DIContainer());
});

$this->create('crate_it_get_items', '/crate/get_items')->get()->action(function($params){
    App::main('CrateController', 'getItems', $params, new DIContainer());
});

$this->create('crate_it_add', '/crate/add')->get()->action(function($params) {
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

$this->create('crate_it_publish', '/crate/publish')->post()->action(function($params) {
    App::main('PublishController', 'publishCrate', $params, new DIContainer());
});
