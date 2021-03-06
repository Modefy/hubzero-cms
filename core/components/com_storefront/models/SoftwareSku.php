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
 * @author    Hubzero
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Components\Storefront\Models;

require_once(__DIR__ . DS . 'Sku.php');

/**
 *
 * Storefront SKU class
 *
 */
class SoftwareSku extends Sku
{
	/**
	 * Contructor
	 *
	 * @param  void
	 * @return void
	 */
	public function __construct($sId)
	{
		parent::__construct($sId);
	}

	public function verify()
	{
		parent::verify();

		// Check if the download file is set
		if (empty($this->data->meta['downloadFile']) || !$this->data->meta['downloadFile'])
		{
			throw new \Exception(Lang::txt('Download file must be set'));
		}

		// Check if the download file really exists
		$storefrontConfig = Component::params('com_storefront');
		$dir = $storefrontConfig->get('downloadFolder');
		$file = PATH_ROOT . $dir . DS . $this->data->meta['downloadFile'];

		if (!file_exists($file))
		{
			throw new \Exception(Lang::txt('Download file doesn\'t exist'));
		}
	}

	public function getGlobalDownloadLimit()
	{

	}

}