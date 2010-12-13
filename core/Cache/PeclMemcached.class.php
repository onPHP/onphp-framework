<?php
/***************************************************************************
 *   Copyright (C) 2010 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 *
	 * @see http://ru.php.net/memcached
	 * @see http://pecl.php.net/package/memcached
	 *
	 * @ingroup Cache
	**/
	final class PeclMemcached extends BaseMemcache
	{		
		/**
		 * @return PeclMemcached
		**/
		public static function create()
		{
			return new self();
		}

		public function __construct()
		{
			
		}

		public function __destruct()
		{
			
		}

		/**
		 * @return PeclMemcached
		**/
		public function clean()
		{
			
		}

		public function increment($key, $value)
		{
			
		}

		public function decrement($key, $value)
		{
			
		}

		public function getList($indexes)
		{
			
		}

		public function get($index)
		{
			
		}

		public function delete($index)
		{
			
		}

		public function append($key, $data)
		{
			
		}

		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			
		}
	}
?>