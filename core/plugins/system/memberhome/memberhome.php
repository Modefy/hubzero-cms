<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   HUBzero
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

// no direct access
defined('_HZEXEC_') or die();

/**
 * Redirect to Member Home Page
 */
class plgSystemMemberHome extends \Hubzero\Plugin\Plugin
{
	/**
	 * Constructor
	 *
	 * @return  boolean
	 */
	public function onAfterRoute()
	{
		$task = Request::getVar('task', 'none');

		if (User::isGuest() || !App::isSite() || $task == 'user.logout')
		{
			return false;
		}

		/*$ignoredURLs = (string) $this->params->get('ignore_urls', '');

		if ($ignoredURLs)
		{
			$ignoredURLArray = explode("\r\n",$ignoredURLs);

			$fullURL = Request::current();

			foreach ($ignoredURLArray as $str)
			{
				$pos = strpos($fullURL, $str);
				if ($pos !== false)
				{
					return false;
				}
			}
		}

		$ignoredOptions = (string) $this->params->get('ignore_options', '');

		if ($ignoredOptions)
		{
			$option = Request::getCmd('option', '');
			$ignoredOptionsArray = explode("\r\n", $ignoredOptions);

			foreach ($ignoredOptionsArray as $str)
			{
				if ($str == $option)
				{
					return false;
				}
			}
		}*/

		$menuId = $this->params->get('menuId', 0);

		if (!$menuId)
		{
			return false;
		}

		$menu = App::get('menu');
		$activeMenu  = $menu->getActive();
		$defaultMenu = $menu->getDefault();

		if ($activeMenu == $defaultMenu)
		{
			$menu->setActive($menuId);
			$menu->setDefault($menuId, $defaultMenu->language);
			$item = $menu->getItem($menuId);
			$vars = $item->query;
			$vars['Itemid'] = $menuId;

			foreach ($vars as $key => $var)
			{
				Request::setVar($key, $var);
			}

			//Request::set($vars, 'method', true);
			//App::redirect(Route::url('index.php?Itemid=' . $menuId, false));
		}
		return true;
	}
}
