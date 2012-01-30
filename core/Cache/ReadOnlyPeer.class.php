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
	 * Cache with read-only access.
	 *
	 * @ingroup Cache
	**/
	final class ReadOnlyPeer extends CachePeer
	{
		/**
		 * @var CachePeer
		 */
		private $innerPeer = null;

		/**
		 * @return ReadOnlyPeer
		 */
		public static function create(CachePeer $peer)
		{
			return new ReadOnlyPeer($peer);
		}

		public function __construct(CachePeer $peer)
		{
			$this->innerPeer = $peer;
		}

		public function isAlive()
		{
			return $this->innerPeer->isAlive();
		}

		public function mark($className)
		{
			return $this->innerPeer->mark($className);
		}

		public function get($key)
		{
			return $this->innerPeer->get($key);
		}

		public function getList($indexes)
		{
			return $this->innerPeer->getList($indexes);
		}

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
