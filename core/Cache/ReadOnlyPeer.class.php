<?php
	/***************************************************************************
	 *   Copyright (C) 2011 by Alexander A. Klestov                            *
	 *                                                                         *
	 *   This program is free software; you can redistribute it and/or modify  *
	 *   it under the terms of the GNU Lesser General Public License as        *
	 *   published by the Free Software Foundation; either version 3 of the    *
	 *   License, or (at your option) any later version.                       *
	 *                                                                         *
	 ***************************************************************************/

	/**
	 * Memcached-based cache with read-only access.
	 *
	 * @ingroup Cache
	**/
	final class ReadOnlyPeer extends Memcached
	{
		public function clean()
		{
			throw new UnsupportedMethodException();
		}

		public function increment($key, $value)
		{
			throw new UnsupportedMethodException();
		}

		public function decrement($key, $value)
		{
			throw new UnsupportedMethodException();
		}

		public function delete($index, $time = null)
		{
			throw new UnsupportedMethodException();
		}

		public function append($key, $data)
		{
			throw new UnsupportedMethodException();
		}

		protected function store(
			$method, $index, $value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			throw new UnsupportedMethodException();
		}
	}
?>
