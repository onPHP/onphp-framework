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

	class SequentialCache extends CachePeer
	{
		protected $list = array();

		public function __construct()
		{
			$list = func_get_args();

			foreach ($list as $cache) {
				$this->addPeer($cache);
			}
		}

		public function addPeer($peer)
		{
			$this->list[] = $peer;

			return $this;
		}

		public function get($key)
		{
			foreach ($this->list as $val) {
				/**
				* @var $val CachePeer 
				*/
				try {
					
					if ($val->isAlive()) {
						$result = $val->get($key);
						if ($val->isAlive()) {
							return $result;
						}
					}
				} catch (Exception $e) {
					//go next...
				}
			}
			throw new RuntimeException("All peers are dead");
		}

		protected function store($action, $key, $value, $expires = Cache::EXPIRES_MEDIUM)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		public function append($key, $data)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		public function decrement($key, $value)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		public function delete($key)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}


		public function increment($key, $value)
		{
			return $this->foreachItem(__METHOD__, func_get_args());
		}

		private function foreachItem($method, $args)
		{
			foreach ($this->list as $val) {
				call_user_func_array(array($val, $method), $args);
			}
			
			return $this;
		}
	}