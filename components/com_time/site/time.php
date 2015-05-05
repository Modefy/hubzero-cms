<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Sam Wilson <samwilson@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Time\Site;

require_once         __DIR__  . DS . 'controllers' . DS . 'base.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'hub.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'task.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'record.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'contact.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'permissions.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'proxy.php';
require_once dirname(__DIR__) . DS . 'models'      . DS . 'liaison.php';
require_once dirname(__DIR__) . DS . 'helpers'     . DS . 'filters.php';

$controllerName = Request::getCmd('controller', Request::getCmd('view', 'overview'));
if (!file_exists(__DIR__ . DS . 'controllers' . DS . $controllerName . '.php'))
{
	$controllerName = 'overview';
}
require_once __DIR__ . DS . 'controllers' . DS . $controllerName . '.php';
$controllerName = __NAMESPACE__ . '\\Controllers\\' . ucfirst($controllerName);

// Instantiate controller
$controller = new $controllerName();
$controller->execute();
$controller->redirect();