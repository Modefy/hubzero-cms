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
 * @package   hubzero-cms
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

Submenu::addEntry(
	Lang::txt('COM_GROUPS_PAGES'),
	Route::url('index.php?option=com_groups&controller=pages&gid=' . $this->group->get('cn')),
	Request::getVar('controller', 'pages') == 'pages'
);

Submenu::addEntry(
	Lang::txt('COM_GROUPS_PAGES_CATEGORIES'),
	Route::url('index.php?option=com_groups&controller=categories&gid=' . $this->group->get('cn')),
	Request::getVar('controller', 'pages') == 'categories'
);

// load group params
$config = Component::params('com_groups');

// only show modules if Super group
if ($this->group->isSuperGroup() || $config->get('page_modules', 0))
{
	Submenu::addEntry(
		Lang::txt('COM_GROUPS_PAGES_MODULES'),
		Route::url('index.php?option=com_groups&controller=modules&gid=' . $this->group->get('cn')),
		Request::getVar('controller', 'pages') == 'modules'
	);
}
