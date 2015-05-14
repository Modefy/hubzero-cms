<?php
/**
 * @package     hubzero-cms
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
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
 */
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Controller class for OAIPMH config
 */
class OaipmhControllerConfig extends \Hubzero\Component\AdminController
{
	/**
	 * Display overview
	 * 
	 * @return  void
	 */
	public function displayTask()
	{
		// display panel
		$this->view->display();
	}

	/**
	 * Display available schemas
	 * 
	 * @return  void
	 */
	public function schemasTask()
	{
		require_once(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'service.php');

		// display panel
		$this->view
			->set('service', new \Components\Oaipmh\Models\Service())
			->display();
	}
}