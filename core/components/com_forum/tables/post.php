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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

namespace Components\Forum\Tables;

use User;
use Lang;
use Date;

/**
 * Table class for forum posts
 */
class Post extends \JTable
{
	/**
	 * Constructor
	 *
	 * @param   object  &$db  Database
	 * @return  void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__forum_posts', 'id', $db);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		$type = ($this->parent) ? 'post' : 'thread';
		return 'com_forum.' . $type . '.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @param   object   $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 * @return  integer  The id of the asset's parent
	 */
	protected function _getAssetParentId($table = null, $id = null)
	{
		// Initialise variables.
		$assetId = null;
		$db = $this->getDbo();

		if ($assetId === null)
		{
			// Build the query to get the asset id for the parent category.
			$query = $db->getQuery(true);
			$query->select('id');
			$query->from('#__assets');
			$query->where('name = ' . $db->quote('com_forum'));

			// Get the asset id from the database.
			$db->setQuery($query);
			if ($result = $db->loadResult())
			{
				$assetId = (int) $result;
			}
		}

		// Return the asset id.
		if ($assetId)
		{
			return $assetId;
		}
		else
		{
			return parent::_getAssetParentId($table, $id);
		}
	}

	/**
	 * Overloaded bind function.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore.
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 */
	public function bind($src, $ignore = array())
	{
		// Bind the rules.
		if (is_object($src))
		{
			if (isset($src->rules) && is_array($src->rules))
			{
				$rules = new \JAccessRules($src->rules);
				$this->setRules($rules);
			}
		}
		else if (is_array($src))
		{
			if (isset($src['rules']) && is_array($src['rules']))
			{
				$rules = new \JAccessRules($src['rules']);
				$this->setRules($rules);
			}
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Load a record by its alias and bind data to $this
	 *
	 * @param   string   $oid  Record alias
	 * @return  boolean  True upon success, False if errors
	 */
	public function loadByObject($oid=NULL, $scope_id=null, $scope='site')
	{
		$fields = array(
			'object_id' => intval($oid)
		);

		if ($scope_id !== null)
		{
			$fields['scope_id'] = (int) $scope_id;
			$fields['scope']    = (string) $scope;
			$fields['parent']   = 0;
		}

		return parent::load($fields);
	}

	/**
	 * Validate data
	 *
	 * @return     boolean True if data is valid
	 */
	public function check()
	{
		$this->comment = trim($this->comment);

		if (!$this->comment)
		{
			$this->setError(Lang::txt('Please provide a comment'));
			return false;
		}

		if (!$this->title)
		{
			$this->title = substr(strip_tags($this->comment), 0, 70);
			if (strlen($this->title >= 70))
			{
				$this->title .= '...';
			}
		}
		$this->sticky = ($this->sticky) ? $this->sticky : 0;
		$this->closed = ($this->closed) ? $this->closed : 0;

		$this->scope = preg_replace("/[^a-zA-Z0-9]/", '', strtolower($this->scope));
		$this->scope_id = intval($this->scope_id);

		if (!$this->id)
		{
			$this->created    = ($this->created && $this->created != $this->_db->getNullDate()) ? $this->created : Date::toSql();
			$this->created_by = ($this->created_by) ? $this->created_by : User::get('id');
		}
		else
		{
			$this->modified    = ($this->modified && $this->modified != $this->_db->getNullDate()) ? $this->modified : Date::toSql();
			$this->modified_by = ($this->modified_by) ? $this->modified_by : User::get('id');
		}

		if (!$this->parent)
		{
			$this->lft = 0;
			$this->rgt = 1;
		}
		else
		{
			if (!$this->thread)
			{
				$this->thread = $this->parent;
			}
		}

		return true;
	}

	/**
	 * Build a query based off of filters passed
	 *
	 * @param   array   $filters  Filters to construct query from
	 * @return  string  SQL
	 */
	public function buildQuery($filters=array())
	{
		$query  = " FROM $this->_tbl AS c";
		if (isset($filters['group']) && (int) $filters['group'] >= 0)
		{
			$query .= " LEFT JOIN #__xgroups AS g ON g.gidNumber=c.scope_id";
		}
		$query .= " LEFT JOIN #__viewlevels AS a ON c.access=a.id";

		if (isset($filters['thread']) && $filters['thread'] != 0)
		{
			$query .= " WHERE c.thread=" . $this->_db->quote(intval($filters['thread']));
			if (isset($filters['state']))
			{
				if (is_array($filters['state']))
				{
					$filters['state'] = array_map('intval', $filters['state']);
					$query .= " AND c.state IN (" . implode(',', $filters['state']) . ")";
				}
				else if ($filters['state'] >= 0)
				{
					$query .= " AND c.state=" . $this->_db->quote(intval($filters['state']));
				}
			}
			if (!isset($filters['sort']) || !$filters['sort'])
			{
				$filters['sort'] = 'c.id';
			}
			if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
			{
				$filters['sort_Dir'] = 'ASC';
			}
			$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
		}
		else
		{
			$where = array();

			if (isset($filters['state']))
			{
				if (is_array($filters['state']))
				{
					$filters['state'] = array_map('intval', $filters['state']);
					$where[] = "c.state IN (" . implode(',', $filters['state']) . ")";
				}
				else if ($filters['state'] >= 0)
				{
					$where[] = "c.state=" . $this->_db->quote(intval($filters['state']));
				}
			}
			if (isset($filters['access']))
			{
				if (is_array($filters['access']))
				{
					$filters['access'] = array_map('intval', $filters['access']);
					$where[] = "c.access IN (" . implode(',', $filters['access']) . ")";
				}
				else if ($filters['access'] >= 0)
				{
					$where[] = "c.access=" . $this->_db->quote(intval($filters['access']));
				}
			}
			if (isset($filters['sticky']) && (int) $filters['sticky'] != 0)
			{
				$where[] = "c.sticky=" . $this->_db->quote(intval($filters['sticky']));
			}
			if (isset($filters['closed']) && (int) $filters['closed'] >= 0)
			{
				$where[] = "c.closed=" . $this->_db->quote(intval($filters['closed']));
			}
			if (isset($filters['group']) && (int) $filters['group'] >= 0)
			{
				$where[] = "(c.scope_id=" . $this->_db->quote(intval($filters['group'])) . " AND c.scope=" . $this->_db->quote('group') . ")";
			}
			if (isset($filters['scope']) && (string) $filters['scope'])
			{
				$where[] = "c.scope=" . $this->_db->quote(strtolower($filters['scope']));
			}
			if (isset($filters['scope_id']) && (int) $filters['scope_id'] >= 0)
			{
				$where[] = "c.scope_id=" . $this->_db->quote(intval($filters['scope_id']));
			}
			if (isset($filters['scope_sub_id']) && (int) $filters['scope_sub_id'] >= 0)
			{
				$where[] = "(c.scope_sub_id=" . $this->_db->quote(intval($filters['scope_sub_id'])) . " OR c.sticky=1)";
			}
			if (isset($filters['category_id']))
			{
				if (is_array($filters['category_id']) && !empty($filters['category_id']))
				{
					$filters['category_id'] = array_map('intval', $filters['category_id']);
					$where[] = "c.category_id IN (" . implode(',', $filters['category_id']) . ")";
				}
				else if ((int) $filters['category_id'] >= 0)
				{
					$where[] = "c.category_id=" . $this->_db->quote(intval($filters['category_id']));
				}
			}
			if (isset($filters['object_id']) && (int) $filters['object_id'] >= 0)
			{
				$where[] = "c.object_id=" . $this->_db->quote(intval($filters['object_id']));
			}
			if (isset($filters['created_by']) && (int) $filters['created_by'] >= 0)
			{
				$where[] = "c.created_by=" . $this->_db->quote(intval($filters['created_by']));
			}
			//if (!isset($filters['authorized']) || !$filters['authorized']) {
			//	$query .= "c.access=0 AND ";
			//}
			if (isset($filters['search']) && $filters['search'] != '')
			{
				$where[] = "(LOWER(c.title) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . "
						OR LOWER(c.comment) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . ")";
			}
			if (isset($filters['parent']) && (int) $filters['parent'] >= 0)
			{
				$where[] = "c.parent=" . $this->_db->quote(intval($filters['parent']));
			}
			if (isset($filters['thread']) && (int) $filters['thread'] >= 0)
			{
				$where[] = "c.thread=" . $this->_db->quote(intval($filters['thread']));
			}

			if (count($where) > 0)
			{
				$query .= " WHERE ";
				$query .= implode(" AND ", $where);
			}

			//if (isset($filters['limit']) && $filters['limit'] != 0)
			if (!isset($filters['count']) || !$filters['count'])
			{
				if (isset($filters['sticky']) && $filters['sticky'] == false)
				{
					if (!isset($filters['sort']) || !$filters['sort'])
					{
						$filters['sort'] = 'activity DESC, c.id';
					}
				}
				else
				{
					if (!isset($filters['sort']) || !$filters['sort'])
					{
						$filters['sort'] = 'c.sticky DESC, activity DESC, c.id';
					}
				}
				if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
				{
					$filters['sort_Dir'] = 'DESC';
				}
				$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
			}
		}
		return $query;
	}

	/**
	 * Get a record count
	 *
	 * @param   array    $filters  Filters to construct query from
	 * @return  integer
	 */
	public function getCount($filters=array())
	{
		$filters['limit'] = 0;
		$filters['count'] = true;

		$query = "SELECT COUNT(*) " . $this->buildQuery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to construct query from
	 * @return  array
	 */
	public function getRecords($filters=array())
	{
		$query = "SELECT c.*";
		if (isset($filters['group']) && (int) $filters['group'] >= 0)
		{
			$query .= ", g.cn AS group_alias";
		}

		if (!isset($filters['thread']) || $filters['thread'] == 0)
		{
			$query .= ", (SELECT COUNT(*) FROM $this->_tbl AS r WHERE r.parent=c.id AND r.state<2) AS replies ";
			$query .= ", (CASE WHEN c.last_activity != '0000-00-00 00:00:00' THEN c.last_activity ELSE c.created END) AS activity";
		}
		$query .= ", a.title AS access_level";
		$query .= $this->buildQuery($filters);

		if (isset($filters['limit']) && $filters['limit'] != 0)
		{
			$query .= ' LIMIT ' . intval($filters['start']) . ',' . intval($filters['limit']);
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to construct query from
	 * @return  array
	 */
	public function getLatestPosts($filters=array())
	{
		$query = "SELECT c.*";
		if (isset($filters['group']) && (int) $filters['group'] >= 0)
		{
			$query .= ", g.cn AS group_alias";
		}
		if (!isset($filters['parent']) || $filters['parent'] == 0)
		{
			$query .= ", (SELECT COUNT(*) FROM $this->_tbl AS r WHERE r.parent=c.id AND r.state<2) AS replies ";
			$query .= ", (CASE WHEN c.last_activity != '0000-00-00 00:00:00' THEN c.last_activity ELSE c.created END) AS activity";
		}
		$query .= ", a.title AS access_level";
		$query .= " FROM $this->_tbl AS c";
		$query .= " LEFT JOIN #__viewlevels AS a ON c.access=a.id";

		$where = array();

		if (isset($filters['state']))
		{
			if (is_array($filters['state']))
			{
				$filters['state'] = array_map('intval', $filters['state']);
				$where[] = "c.state IN (" . implode(',', $filters['state']) . ")";
			}
			else if ($filters['state'] >= 0)
			{
				$where[] = "c.state=" . $this->_db->quote(intval($filters['state']));
			}
		}
		if (isset($filters['access']))
		{
			if (is_array($filters['access']))
			{
				$filters['access'] = array_map('intval', $filters['access']);
				$where[] = "c.access IN (" . implode(',', $filters['access']) . ")";
			}
			else if ($filters['access'] >= 0)
			{
				$where[] = "c.access=" . $this->_db->quote(intval($filters['access']));
			}
		}
		if (isset($filters['sticky']) && (int) $filters['sticky'] != 0)
		{
			$where[] = "c.sticky=" . $this->_db->quote(intval($filters['sticky']));
		}

		if (isset($filters['closed']) && (int) $filters['closed'] >= 0)
		{
			$where[] = "c.closed=" . $this->_db->quote(intval($filters['closed']));
		}
		if (isset($filters['scope']) && (string) $filters['scope'])
		{
			$where[] = "c.scope=" . $this->_db->quote(strtolower($filters['scope']));
		}
		if (isset($filters['scope_id']) && (int) $filters['scope_id'] >= 0)
		{
			$where[] = "c.scope_id=" . $this->_db->quote(intval($filters['scope_id']));
		}
		if (isset($filters['scope_sub_id']) && (int) $filters['scope_sub_id'] >= 0)
		{
			$where[] = "(c.scope_sub_id=" . $this->_db->quote(intval($filters['scope_sub_id'])) . " OR c.sticky=1)";
		}
		if (isset($filters['category_id']) && (int) $filters['category_id'] >= 0)
		{
			$where[] = "c.category_id=" . $this->_db->quote(intval($filters['category_id']));
		}
		if (isset($filters['object_id']) && (int) $filters['object_id'] >= 0)
		{
			$where[] = "c.object_id=" . $this->_db->quote(intval($filters['object_id']));
		}

		if (isset($filters['search']) && $filters['search'] != '')
		{
			$where[] = "(LOWER(c.title) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . "
					OR LOWER(c.comment) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . ")";
		}
		//if (isset($filters['parent']) && (int) $filters['parent'] >= 0)
		//{
			$where[] = "c.parent>0"; //. $this->_db->quote(intval($filters['parent']));
		//}

		if (count($where) > 0)
		{
			$query .= " WHERE ";
			$query .= implode(" AND ", $where);
		}

		if (isset($filters['limit']) && $filters['limit'] != 0)
		{
			if (isset($filters['sticky']) && $filters['sticky'] == false)
			{
				if (!isset($filters['sort']) || !$filters['sort'])
				{
					$filters['sort'] = 'activity DESC, c.id';
				}
				if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
				{
					$filters['sort_Dir'] = 'DESC';
				}
				$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
			}
			else
			{
				if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
				{
					$filters['sort_Dir'] = 'DESC';
				}
				$query .= " ORDER BY c.sticky DESC, activity DESC, c.id " . $filters['sort_Dir'];
			}
		}

		if ($filters['limit'] != 0)
		{
			$query .= ' LIMIT ' . intval($filters['start']) . ',' . intval($filters['limit']);
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Build a query based off of filters passed
	 *
	 * @param   array   $filters  Filters to construct query from
	 * @return  string  SQL
	 */
	private function _buildQuery($filters=array())
	{
		$query  = " FROM $this->_tbl AS c";
		if (isset($filters['replies']))
		{
			$query .= " LEFT JOIN $this->_tbl AS p ON p.id=c.parent";
		}
		$query .= " LEFT JOIN #__xprofiles AS u ON u.uidNumber=c.created_by";
		$query .= " LEFT JOIN #__viewlevels AS a ON c.access=a.id";

		$where = array();

		if (isset($filters['replies']))
		{
			$where[] = "c.parent != 0";
		}
		if (isset($filters['state']))
		{
			if (is_array($filters['state']))
			{
				$filters['state'] = array_map('intval', $filters['state']);
				$where[] = "c.state IN (" . implode(',', $filters['state']) . ")";
			}
			else if ($filters['state'] >= 0)
			{
				$where[] = "c.state=" . $this->_db->quote(intval($filters['state']));
			}
		}
		if (isset($filters['access']))
		{
			if (is_array($filters['access']))
			{
				$filters['access'] = array_map('intval', $filters['access']);
				$where[] = "c.access IN (" . implode(',', $filters['access']) . ")";
			}
			else if ($filters['access'] >= 0)
			{
				$where[] = "c.access=" . $this->_db->quote(intval($filters['access']));
			}
		}
		if (isset($filters['sticky']) && (int) $filters['sticky'] != 0)
		{
			$where[] = "c.sticky=" . $this->_db->quote(intval($filters['sticky']));
		}
		if (isset($filters['closed']) && (int) $filters['closed'] >= 0)
		{
			$where[] = "c.closed=" . $this->_db->quote(intval($filters['closed']));
		}
		if (isset($filters['group']) && (int) $filters['group'] >= 0)
		{
			$where[] = "(c.scope_id=" . $this->_db->quote(intval($filters['group'])) . " AND c.scope=" . $this->_db->quote('group') . ")";
		}
		if (isset($filters['scope']) && $filters['scope'])
		{
			if (is_array($filters['scope']))
			{
				foreach ($filters['scope'] as $k => $scope)
				{
					$filters['scope'][$k] = $this->_db->quote(strtolower((string) $scope));
				}

				$where[] = "c.scope IN (" . implode(',', $filters['scope']) . ")";
			}
			else
			{
				$where[] = "c.scope=" . $this->_db->quote(strtolower((string) $filters['scope']));
			}
		}
		if (isset($filters['scope_id']))
		{
			if (is_array($filters['scope_id']) && count($filters['scope_id']) > 0)
			{
				$filters['scope_id'] = array_map('intval', $filters['scope_id']);

				$where[] = "c.scope_id IN (" . implode(',', $filters['scope_id']) . ")";
			}
			else if ((int) $filters['scope_id'] >= 0)
			{
				$where[] = "c.scope_id=" . $this->_db->quote(intval($filters['scope_id']));
			}
		}
		if (isset($filters['scope_sub_id']) && (int) $filters['scope_sub_id'] >= 0)
		{
			$where[] = "(c.scope_sub_id=" . $this->_db->quote(intval($filters['scope_sub_id'])) . " OR c.sticky=1)";
		}

		if (isset($filters['category_id']) && (int) $filters['category_id'] >= 0)
		{
			$where[] = "c.category_id=" . $this->_db->quote(intval($filters['category_id']));
		}
		if (isset($filters['replies']))
		{
			if (isset($filters['created_by']) && (int) $filters['created_by'] >= 0)
			{
				$where[] = "p.created_by=" . $this->_db->quote(intval($filters['created_by']));
				$where[] = "c.created_by!=" . $this->_db->quote(intval($filters['created_by']));
			}
		}
		else
		{
			if (isset($filters['created_by']) && (int) $filters['created_by'] >= 0)
			{
				$where[] = "c.created_by=" . $this->_db->quote(intval($filters['created_by']));
			}
		}

		if (isset($filters['object_id']) && (int) $filters['object_id'] >= 0)
		{
			$where[] = "c.object_id=" . $this->_db->quote(intval($filters['object_id']));
		}
		if (isset($filters['search']) && $filters['search'] != '')
		{
			$where[] = "(LOWER(c.title) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . "
					OR LOWER(c.comment) LIKE " . $this->_db->quote('%' . strtolower($filters['search']) . '%') . ")";
		}
		if (isset($filters['parent']) && (int) $filters['parent'] >= 0)
		{
			$where[] = "c.parent=" . $this->_db->quote(intval($filters['parent']));
		}
		if (isset($filters['thread']) && (int) $filters['thread'] >= 0)
		{
			$where[] = "c.thread=" . $this->_db->quote(intval($filters['thread']));
		}
		if (isset($filters['start_at']) && $filters['start_at'])
		{
			$where[] = "c.created >" . $this->_db->quote($filters['start_at']);
		}

		if (isset($filters['id']) && $filters['id'])
		{
			if (!is_array($filters['id']))
			{
				$filters['id'] = array($filters['id']);
			}
			$filters['id'] = array_map('intval', $filters['id']);
			$where[] = "c.id IN (" . implode(',', $filters['id']) . ")";
		}

		if (count($where) > 0)
		{
			$query .= " WHERE ";
			$query .= implode(" AND ", $where);
		}

		if (!isset($filters['count']) || !$filters['count'])
		{
			if (isset($filters['sticky']) && $filters['sticky'] == false)
			{
				if (!isset($filters['sort']) || !$filters['sort'])
				{
					$filters['sort'] = 'activity DESC, c.id';
				}
			}
			else
			{
				if (!isset($filters['sort']) || !$filters['sort'])
				{
					$filters['sort'] = 'c.sticky DESC, activity DESC, c.id';
				}
			}
			if (!isset($filters['sort_Dir']) || !in_array(strtoupper($filters['sort_Dir']), array('ASC', 'DESC')))
			{
				$filters['sort_Dir'] = 'DESC';
			}
			$query .= " ORDER BY " . $filters['sort'] . " " . $filters['sort_Dir'];
		}

		return $query;
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to construct query from
	 * @return  array
	 */
	public function count($filters=array())
	{
		$filters['count'] = true;

		$query  = "SELECT COUNT(c.id), a.title AS access_level";
		$query .= $this->_buildQuery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 *
	 * @param   array  $filters  Filters to construct query from
	 * @return  array
	 */
	public function find($filters=array())
	{
		$query = "SELECT c.*, u.name, u.picture";
		if (!isset($filters['parent']) || $filters['parent'] == 0)
		{
			$query .= ", (SELECT COUNT(*) FROM $this->_tbl AS r WHERE r.parent=c.id AND r.state<2) AS replies ";
			$query .= ", (CASE WHEN c.last_activity != '0000-00-00 00:00:00' THEN c.last_activity ELSE c.created END) AS activity";
		}
		$query .= ", a.title AS access_level";
		$query .= $this->_buildQuery($filters);

		if ($filters['limit'] != 0)
		{
			if (!isset($filters['start']))
			{
				$filters['start'] = 0;
			}
			$query .= ' LIMIT ' . intval($filters['start']) . ',' . intval($filters['limit']);
		}

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get a list of all participants in a thread
	 *
	 * @param   array  $filters  Filters to build query from
	 * @return  array
	 */
	public function getParticipants($filters=array())
	{
		$query = "SELECT DISTINCT c.anonymous, c.created_by, u.name
					FROM $this->_tbl AS c
					LEFT JOIN `#__users` AS u ON c.created_by=u.id
					WHERE ";

		if (isset($filters['category_id']))
		{
			$where[] = "c.category_id = " . $this->_db->quote($filters['category_id']);
		}
		$where[] = "c.state IN (1, 3)";
		if (isset($filters['parent']))
		{
			$where[] = "(c.parent = " . $this->_db->quote($filters['parent']) . " OR c.id = " . $this->_db->quote($filters['parent']) . ")";
		}
		if (isset($filters['thread']))
		{
			$where[] = "c.thread = " . $this->_db->quote($filters['thread']);
		}

		$query .= implode(" AND ", $where);

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get the last post in a thread
	 *
	 * @param   integer  $parent  Thread ID
	 * @return  object
	 */
	public function getLastPost($thread=null)
	{
		if (!$thread)
		{
			$thread = $this->thread;
		}
		if (!$thread)
		{
			return null;
		}

		$query = "SELECT r.* FROM $this->_tbl AS r WHERE r.thread=" . $this->_db->quote($thread) . " AND r.state=1";
		if (User::isGuest())
		{
			$query .= " AND r.access=0";
		}
		else
		{
			$query .= " AND r.access IN (0, 1)";
		}
		$query .= " ORDER BY id DESC LIMIT 1";

		$this->_db->setQuery($query);
		$obj = $this->_db->loadObject();
		if (is_array($obj))
		{
			return $obj[0];
		}
		return $obj;
	}

	/**
	 * Get the last activity for a category
	 *
	 * @param   integer  $group_id     Group ID
	 * @param   integer  $category_id  Category ID
	 * @return  object
	 */
	public function getLastActivity($scope_id=null, $scope='site', $category_id=null)
	{
		$query = "SELECT r.* FROM $this->_tbl AS r";
		$where = array();
		if ($scope_id !== null)
		{
			$where[] = "r.scope_id=" . $this->_db->quote($scope_id);
		}
		$where[] = "r.scope=" . $this->_db->quote($scope);
		$where[] = "r.state=" . $this->_db->quote(1);
		if (User::isGuest())
		{
			$where[] = "r.access=0";
		}
		else
		{
			$where[] = "r.access IN (0, 1)";
		}
		if ($category_id !== null)
		{
			$where[] = "r.category_id=" . $this->_db->quote($category_id);
		}
		if (count($where) > 0)
		{
			$query .= " WHERE " . implode(" AND ", $where);
		}
		$query .= " ORDER BY id DESC LIMIT 1";

		$this->_db->setQuery($query);
		$obj = $this->_db->loadObject();
		if (is_array($obj))
		{
			return $obj[0];
		}
		return $obj;
	}

	/**
	 * Delete replies to a post
	 *
	 * @param   integer  $parent  Thread ID
	 * @return  boolean  True on success
	 */
	public function deleteReplies($parent=null)
	{
		if (!$parent)
		{
			$parent = $this->parent;
		}
		if (!$parent)
		{
			return null;
		}

		$this->_db->setQuery("DELETE FROM $this->_tbl WHERE parent=" . $this->_db->quote($parent));
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Update all replies to a post
	 *
	 * @param   array    $data    Data to update posts with
	 * @param   integer  $parent  Parent ID
	 * @return  boolean  True on success
	 */
	public function updateReplies($data=array(), $parent=null)
	{
		if (!$parent)
		{
			$parent = $this->parent;
		}
		if (!$parent)
		{
			return false;
		}

		if (empty($data))
		{
			return false;
		}

		$set = array();
		foreach ($data as $key => $val)
		{
			$set[] = $key . '=' . $this->_db->quote($val);
		}
		$values = implode(', ', $set);

		$this->_db->setQuery("SELECT lft, rgt, thread FROM $this->_tbl WHERE id=" . $this->_db->quote($parent));
		$row = $this->_db->loadObject();

		$this->_db->setQuery("UPDATE $this->_tbl SET $values WHERE parent=" . $this->_db->quote($parent) . " OR (thread=" . $this->_db->quote($row->thread) . " AND lft > " . $this->_db->quote($row->lft) . " AND rgt < " . $this->_db->quote($row->rgt) . ")"); // parent=" . $this->_db->quote($parent));
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Set a new category for all records of a previous category
	 *
	 * @param   integer  $old       Old category ID
	 * @param   integer  $nw        New category ID
	 * @param   integer  $group_id  Group ID
	 * @return  boolean  True on success
	 */
	public function updateCategory($old=null, $nw=null, $group_id=0, $scope='site')
	{
		if ($old === null)
		{
			$old = $this->category_id;
		}
		if ($nw === null || $old === null)
		{
			return false;
		}

		$this->_db->setQuery("UPDATE $this->_tbl SET category_id=" . $this->_db->quote($nw) . " WHERE category_id=" . $this->_db->quote($old) . " AND scope_id=" . $this->_db->quote($scope_id) . " AND scope=" . $this->_db->quote($scope));
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Delete all records in a category
	 *
	 * @param   integer  $oid  Record ID
	 * @return  boolean  True on success
	 */
	public function deleteByCategory($oid=null)
	{
		$oid = intval($oid);
		if ($oid === null)
		{
			return false;
		}

		$query = 'DELETE FROM ' . $this->_db->quoteName($this->_tbl) . ' WHERE category_id = ' . $this->_db->quote($oid);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Delete a record and any children
	 *
	 * @param   integer  $oid  Record ID
	 * @return  boolean  True on success
	 */
	public function delete($oid=null)
	{
		$k = $this->_tbl_key;
		if ($oid)
		{
			$this->$k = intval($oid);
		}

		$this->load($this->$k);
		if (!$this->parent)
		{
			$query = 'DELETE FROM ' . $this->_db->quoteName($this->_tbl) . ' WHERE parent = ' . $this->_db->quote($this->$k);
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return parent::delete($oid);
	}

	/**
	 * Set the state of posts by the category
	 *
	 * @param   integer  $cat    Category ID
	 * @param   integer  $state  State to set (0, 1, 2)
	 * @return  boolean  True on success
	 */
	public function setStateByCategory($cat=null, $state=null)
	{
		if ($cat === null)
		{
			$cat = $this->category_id;
		}
		if ($state === null || $cat === null)
		{
			return false;
		}

		if (is_array($cat))
		{
			$cat = array_map('intval', $cat);
			$cat = implode(',', $cat);
		}
		else
		{
			$cat = intval($cat);
		}

		$this->_db->setQuery("UPDATE $this->_tbl SET state=" . $this->_db->quote($state) . " WHERE category_id IN ($cat)");
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Get the thread starter
	 *
	 * @param   integer  $id  Parent to look up
	 * @return  object
	 */
	public function getThread($id = null)
	{
		$thread = new self($this->_db);
		$thread->load($id);

		// Return the asset id.
		if ($thread->parent)
		{
			return $this->getThread($thread->parent);
		}

		return $thread;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  True to update null values as well.
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// If the primary key is empty, then we assume we are inserting a new node into the
		// tree.  From this point we would need to determine where in the tree to insert it.
		if (empty($this->$k) && $this->parent)
		{
			$parent = new self($this->_db);
			$parent->load($this->parent);

			if (!$parent)
			{
				$this->setError(Lang::txt('Parent node does not exist.'));
				return false;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!($repositionData = $this->_getTreeRepositionData($parent, 2, 'last-child')))
			{
				// Error message set in getNode method.
				return false;
			}

			// Shift left values.
			$this->_db->setQuery("UPDATE $this->_tbl SET lft = lft + 2 WHERE " . $repositionData->left_where . " AND scope=" . $this->_db->quote($parent->scope) . " AND scope_id=" . $this->_db->quote($parent->scope_id) . " AND object_id=" . $this->_db->quote($parent->object_id));
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Shift right values.
			$this->_db->setQuery("UPDATE $this->_tbl SET rgt = rgt + 2 WHERE " . $repositionData->right_where . " AND scope=" . $this->_db->quote($parent->scope) . " AND scope_id=" . $this->_db->quote($parent->scope_id) . " AND object_id=" . $this->_db->quote($parent->object_id));
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			$this->lft = $repositionData->new_lft;
			$this->rgt = $repositionData->new_rgt;
		}

		// Store the row to the database.
		$result = parent::store($updateNulls);
		if ($result)
		{
			if ($this->parent == 0)
			{
				$this->_db->setQuery("UPDATE $this->_tbl SET thread=id WHERE parent=0 AND id=" . $this->_db->quote($this->id));
				if (!$this->_db->query())
				{
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				$this->thread = $this->id;
			}
		}
		return $result;
	}

	/**
	 * Method to get various data necessary to make room in the tree at a location
	 * for a node and its children.  The returned data object includes conditions
	 * for SQL WHERE clauses for updating left and right id values to make room for
	 * the node as well as the new left and right ids for the node.
	 *
	 * @param   object   $referenceNode  A node object with at least a 'lft' and 'rgt' with
	 *                                   which to make room in the tree around for a new node.
	 * @param   integer  $nodeWidth      The width of the node for which to make room in the tree.
	 * @param   string   $position       The position relative to the reference node where the room
	 *                                   should be made.
	 * @return  mixed    Boolean false on failure or data object on success.
	 */
	protected function _getTreeRepositionData($referenceNode, $nodeWidth, $position = 'before')
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) && isset($referenceNode->lft) && isset($referenceNode->rgt))
		{
			return false;
		}

		// A valid node cannot have a width less than 2.
		if ($nodeWidth < 2)
		{
			return false;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$data = new \stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case 'first-child':
				$data->left_where		= 'lft > '.$referenceNode->lft;
				$data->right_where		= 'rgt >= '.$referenceNode->lft;

				$data->new_lft			= $referenceNode->lft + 1;
				$data->new_rgt			= $referenceNode->lft + $nodeWidth;
			break;

			case 'last-child':
				$data->left_where		= 'lft > '.($referenceNode->rgt);
				$data->right_where		= 'rgt >= '.($referenceNode->rgt);

				$data->new_lft			= $referenceNode->rgt;
				$data->new_rgt			= $referenceNode->rgt + $nodeWidth - 1;
			break;

			case 'before':
				$data->left_where		= 'lft >= '.$referenceNode->lft;
				$data->right_where		= 'rgt >= '.$referenceNode->lft;

				$data->new_lft			= $referenceNode->lft;
				$data->new_rgt			= $referenceNode->lft + $nodeWidth - 1;
			break;

			default:
			case 'after':
				$data->left_where		= 'lft > '.$referenceNode->rgt;
				$data->right_where		= 'rgt > '.$referenceNode->rgt;

				$data->new_lft			= $referenceNode->rgt + 1;
				$data->new_rgt			= $referenceNode->rgt + $nodeWidth;
			break;
		}

		return $data;
	}

	/**
	 * Method to get a node and all its child nodes.
	 *
	 * @param   integer  $pk          Primary key of the node for which to get the tree.
	 * @return  mixed    Boolean false on failure or array of node objects on success.
	 */
	public function countTree($pk = null, $filters=array())
	{
		if (!isset($filters['state']))
		{
			$filters['state'] = 1;
		}
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$query = "SELECT COUNT(n.id)
					FROM $this->_tbl AS n
					WHERE n.thread=" . (int) $pk;
		if (isset($filters['start_at']) && $filters['start_at'])
		{
			$query .= " AND n.created >" . $this->_db->quote($filters['start_at']);
		}
		if (isset($filters['state']))
		{
			if (is_array($filters['state']))
			{
				$filters['state'] = array_map('intval', $filters['state']);
				$query .= " AND n.state IN (" . implode(',', $filters['state']) . ")";
			}
			else if ($filters['state'] >= 0)
			{
				$query .= " AND n.state=" . $this->_db->quote(intval($filters['state']));
			}
		}

		$this->_db->setQuery($query);
		$tree = $this->_db->loadResult();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError(Lang::txt('Failed to get tree.'));
			return false;
		}

		return $tree;
	}

	/**
	 * Method to get a node and all its child nodes.
	 *
	 * @param   integer  $pk          Primary key of the node for which to get the tree.
	 * @return  mixed    Boolean false on failure or array of node objects on success.
	 */
	public function getTree($pk = null, $filters=array())
	{
		if (!isset($filters['state']))
		{
			$filters['state'] = 1;
		}
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$query = "SELECT n.*, 0 AS replies
					FROM $this->_tbl AS n
					WHERE n.thread=" . (int) $pk;
		if (isset($filters['start_at']) && $filters['start_at'])
		{
			$query .= " AND n.created >" . $this->_db->quote($filters['start_at']);
		}
		if (isset($filters['state']))
		{
			if (is_array($filters['state']))
			{
				$filters['state'] = array_map('intval', $filters['state']);
				$query .= " AND n.state IN (" . implode(',', $filters['state']) . ")";
			}
			else if ($filters['state'] >= 0)
			{
				$query .= " AND n.state=" . $this->_db->quote(intval($filters['state']));
			}
		}
		if (isset($filters['access']))
		{
			if (is_array($filters['access']))
			{
				$filters['access'] = array_map('intval', $filters['access']);
				$query .= " AND n.access IN (" . implode(',', $filters['access']) . ")";
			}
			else if ($filters['access'] >= 0)
			{
				$query .= " AND n.access=" . $this->_db->quote(intval($filters['access']));
			}
		}
		$query .= " ORDER BY n.created ASC";

		if (isset($filters['limit']) && $filters['limit'] != 0)
		{
			if (!isset($filters['start']))
			{
				$filters['start'] = 0;
			}
			$query .= ' LIMIT ' . intval($filters['start']) . ',' . intval($filters['limit']);
		}

		$this->_db->setQuery($query);
		$tree = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$this->setError(Lang::txt('Failed to get tree.'));
			return false;
		}

		return $tree;
	}

	/**
	 * Method to get nested set properties for a node in the tree.
	 *
	 * @param   integer  $id   Value to look up the node by.
	 * @param   string   $key  Key to look up the node by.
	 * @return  mixed    Boolean false on failure or node object on success.
	 */
	protected function _getNode($id, $key = null)
	{
		// Determine which key to get the node base on.
		switch ($key)
		{
			case 'parent':
				$k = 'parent';
				break;
			case 'left':
				$k = 'lft';
				break;
			case 'right':
				$k = 'rgt';
				break;
			default:
				$k = $this->_tbl_key;
				break;
		}

		// Get the node data.
		$query = "SELECT " . $this->_tbl_key . ", parent, lft, rgt FROM $this->_tbl WHERE " . $k . ' = '. (int) $id . " LIMIT 1";
		$this->_db->setQuery($query);

		$row = $this->_db->loadObject();

		// Check for a database error or no $row returned
		if ((!$row) || ($this->_db->getErrorNum()))
		{
			$this->setError(Lang::txt('Get node failed'));
			return false;
		}

		// Do some simple calculations.
		$row->numChildren = (int) ($row->rgt - $row->lft - 1) / 2;
		$row->width = (int) $row->rgt - $row->lft + 1;

		return $row;
	}

	/**
	 * Method to determine if a node is a leaf node in the tree (has no children).
	 *
	 * @param   integer  $pk  Primary key of the node to check.
	 * @return  boolean  True if a leaf node.
	 */
	public function isLeaf($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk))
		{
			// Error message set in getNode method.
			return false;
		}

		// The node is a leaf node.
		return (($node->rgt - $node->lft) == 1);
	}
}
