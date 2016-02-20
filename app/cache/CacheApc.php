<?php

/**
 * This class require PECL APC extension
 */
class CacheApc extends Cache
{
	public function __construct()
	{
		$this->keys = array();
		$cache_info = apc_cache_info((extension_loaded('apcu') === true )? '' : 'user' );
		foreach ($cache_info['cache_list'] as $entry)
			if ( extension_loaded('apcu') === true )
				$this->keys[$entry['key']] = $entry['ttl'];
			else
				$this->keys[$entry['info']] = $entry['ttl'];
	}

	/**
	 * @see Cache::_set()
	 */
	protected function _set($key, $value, $ttl = 0)
	{
		return apc_store($key, $value, $ttl);
	}

	/**
	 * @see Cache::_get()
	 */
	protected function _get($key)
	{
		return apc_fetch($key);
	}

	/**
	 * @see Cache::_exists()
	 */
	protected function _exists($key)
	{
		return isset($this->keys[$key]);
	}

	/**
	 * @see Cache::_delete()
	 */
	protected function _delete($key)
	{
		return apc_delete($key);
	}

	/**
	 * @see Cache::_writeKeys()
	 */
	protected function _writeKeys()
	{
	}

	/**
	 * @see Cache::flush()
	 */
	public function flush()
	{
		return apc_clear_cache();
	}
}
