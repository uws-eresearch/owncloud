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


/**
$this->create('crate_it_index', '/hello')->get()->action(function($params) {
    App::main('MyController', 'index', $params, new DIContainer());
});
**/
$this->create('crate_it_index', '/')->get()->action(function($params) {
    // call the index method on the class PageController
    App::main('PageController', 'index', $params, new DIContainer());
});

$this->create('crate_it_get_items', '/crate/get_items')->get()->action(function($params){
    App::main('CrateController', 'get_items', $params, new DIContainer());
});

$this->create('crate_it_add', '/crate/add')->get()->action(function($params) {
    // call the index method on the class PageController
    App::main('CrateController', 'add', $params, new DIContainer());
});

