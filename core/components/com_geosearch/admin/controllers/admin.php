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
 * @author    Brandon Beatty
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Geosearch\Admin\Controllers;

use Hubzero\Component\AdminController;
use Components\Geosearch\Tables\GeosearchMarkers as Markers;

require_once(PATH_CORE . DS . 'components' . DS  . 'com_geosearch' . DS . 'tables' . DS . 'geosearchmarkers.php');

/**
 * Controller class for Libreviews admin
 */
class Admin extends AdminController
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function displayTask()
	{
		// Get the database object and load up the table of markers 
		$db = App::get('db');
		$obj = new Markers($db);

		// Only get markers marked for review
		$filters = array('review' => 1);

		// Pass markers to the view
		$this->view->markers = $obj->getMarkers($filters, 'array');

		$this->view->display();
	}

	public function updateMarkerTask()
	{
		// Get POST data
		$lat = Request::getVar('lat', null);
		$lng = Request::getVar('lng', null);
		$flag = Request::getVar('flag', true);
		$markerID = Request::getInt('markerID', 0);

		// Ensure all fields are set
		if ($flag == false && !is_null($lat) && !is_null($lng) && $markerID > 0)
		{
			// Get the database object and load up the table of markers 
			$db = App::get('db');

			// Update the object
			$sql = "UPDATE #__geosearch_markers SET addressLatitude = ".$db->quote($lat).", addressLongitude = ".$db->quote($lng).", review = ".$db->quote($flag)." WHERE id = {$markerID}";
			$db->setQuery($sql);
			$db->query();
			return true;
			exit();
		}

		return false;
		exit();
	}
}
