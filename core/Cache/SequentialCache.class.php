<?php
/****************************************************************************
 *   Copyright (C) 2012 by Artem Naumenko									*
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	final class SequentialCache extends CachePeer
	{
		/**
		 * List of all peers, including master
		 * @var array of CachePeer
		 */
		protected $list		= array();
		
		/**
		 * List of slaves only
		 * @var array of CachePeer
		 */
		protected $slaves	= array();
		
		/**
		 * @var CachePeer
		 */
		protected $master	= null;

		/**
		 * @param CachePeer $master
		 * @param array $slaves or CachePeer
		 * @return SequentialCache 
		 */
		public static function create(CachePeer $master, $slaves = array())
		{
			return new self($master, $slaves);
		}
		
		/**
		 * @param CachePeer $master
		 * @param array $slaves or CachePeer
		 */
		public function __construct(CachePeer $master, $slaves = array())
		{
			$this->setMaster($master);
			
			foreach ($slaves as $cache) {
				$this->addPeer($cache);
			}
		}
		
		/**
		 * @param CachePeer $master
		 * @return \SequentialCache 
		 */
		public function setMaster(CachePeer $master)
		{
			$this->master = $master;
			$this->list = $this->slaves;
			array_unshift($this->list, $this->master);
			
			return $this;
		}
		
		/**
		 * @param CachePeer $master
		 * @return \SequentialCache 
		 */
		public function addPeer(CachePeer $peer)
		{
			$this->list[]	= $peer;
			$this->slaves[]	= $peer;

			return $this;
		}

		public function get($key)
		{
			foreach ($this->list as $val) {
				/* @var $val CachePeer */
				$result = $val->get($key);
				
				if (
					!empty($result)
					|| $val->isAlive()
				) {
					return $result;
				}
			}
			throw new RuntimeException('All peers are dead');
		}

		public function append($key, $data)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		public function decrement($key, $value)
		{
			throw new UnsupportedMethodException('decrement is not supported');
		}

		public function delete($key)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		public function increment($key, $value)
		{
			throw new UnsupportedMethodException('increment is not supported');
		}
		
		protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		private function foreachItem($method, $args)
		{
			$result = true;
			
			foreach ($this->list as $val) {
				$result &= call_user_func_array(array($val, $method), $args);
			}
			
			return (bool)$result;
		}
	}
