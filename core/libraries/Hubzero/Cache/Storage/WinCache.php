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

namespace Hubzero\Cache\Storage;

use Hubzero\Error\Exception\RuntimeException;
use Hubzero\Cache\Auditor;

/**
 * WinCache storage for Cache manager
 */
class WinCache extends None
{
	/**
	 * Create a new file cache store instance.
	 *
	 * @param   array  $options
	 * @return  void
	 */
	public function __construct(array $options = array())
	{
		parent::__construct($options);

		if (!self::isAvailable())
		{
			throw new RuntimeException('Cannot use WinCache cache storage. WinCache extension is not loaded.');
		}
	}

	/**
	 * Test to see if the cache storage is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public static function isAvailable()
	{
		$test = extension_loaded('wincache') && function_exists('wincache_ucache_get') && !strcmp(ini_get('wincache.ucenabled'), '1');
		return $test;
	}

	/**
	 * Add an item to the cache only if it doesn't already exist
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @param   int     $minutes
	 * @return  void
	 */
	public function add($key, $value, $minutes)
	{
		if ($this->has($key))
		{
			return false;
		}

		return $this->put($key, $value, $minutes);
	}

	/**
	 * Store an item in the cache for a given number of minutes.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @param   int     $minutes
	 * @return  void
	 */
	public function put($key, $value, $minutes)
	{
		return wincache_ucache_set($this->id($key), $value, $minutes * 60);
	}

	/**
	 * Store an item in the cache indefinitely.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 * @return  void
	 */
	public function forever($key, $value)
	{
		return $this->put($key, $value, 0);
	}

	/**
	 * Retrieve an item from the cache by key.
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function get($key)
	{
		return wincache_ucache_get($this->id($key));
	}

	/**
	 * Check if an item exists in the cache
	 *
	 * @param   string  $key
	 * @return  bool
	 */
	public function has($key)
	{
		return wincache_ucache_exists($this->id($key));
	}

	/**
	 * Remove an item from the cache.
	 *
	 * @param   string  $key
	 * @return  bool
	 */
	public function forget($key)
	{
		return wincache_ucache_delete($this->id($key));
	}

	/**
	 * Remove all items from the cache.
	 *
	 * @param   string  $group
	 * @return  void
	 */
	public function clean($group = null)
	{
		$hash = $this->options['hash'];

		$allinfo = wincache_ucache_info();

		foreach ($allinfo['cache_entries'] as $key)
		{
			if (strpos($key['key_name'], $hash . '-cache-' . $group . '-') === 0) // xor $mode != 'group')
			{
				wincache_ucache_delete($key['key_name']);
			}
		}

		return true;
	}

	/**
	 * Force garbage collect expired cache data as items are removed only on get/add/delete/info etc
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public function gc()
	{
		$hash = $this->options['hash'];

		$allinfo = wincache_ucache_info();

		foreach ($allinfo['cache_entries'] as $key)
		{
			if (strpos($key['key_name'], $hash . '-cache-'))
			{
				wincache_ucache_get($key['key_name']);
			}
		}
	}

	/**
	 * Get all cached data
	 *
	 * @return  array
	 */
	public function all()
	{
		$hash = $this->options['hash'];

		$allinfo = wincache_ucache_info();

		$data = array();

		foreach ($allinfo['cache_entries'] as $key)
		{
			$name    = $key['key_name'];
			$namearr = explode('-', $name);

			if ($namearr !== false && $namearr[0] == $hash && $namearr[1] == 'cache')
			{
				$group = $namearr[2];
				if (!isset($data[$group]))
				{
					$item = new Auditor($group);
				}
				else
				{
					$item = $data[$group];
				}
				if (isset($key['value_size']))
				{
					$item->tally($key['value_size'] / 1024);
				}
				else
				{
					// Dummy, WINCACHE version is too low.
					$item->tally(1);
				}

				$data[$group] = $item;
			}
		}

		return $data;
	}
}
