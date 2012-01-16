<?php
/****************************************************************************
 *   Copyright (C) 2011 by Anton E. Lebedevich, Konstantin V. Arkhipov,     *
 *   Evgeny V. Kokovikhin                                                   *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Base common parent for all aggregate caches
	 *
	 * @ingroup Cache
	**/
	abstract class BaseAggregateCache extends SelectivePeer
	{
		protected $peers	= array();
		
		/**
		 * @return BaseAggregateCache
		**/
		public function dropPeer($label)
		{
			if (!isset($this->peers[$label]))
				throw new MissingElementException(
					"there is no peer with '{$label}' label"
				);

			unset($this->peer[$label]);

			return $this;
		}

		/**
		 * @return BaseAggregateCache
		**/
		public function checkAlive()
		{
			$this->alive = false;

			foreach ($this->peers as $label => $peer)
				if ($peer['object']->isAlive())
					$this->alive = true;
				else
					unset($this->peers[$label]);

			return $this->alive;
		}

		abstract protected function guessLabel($key);

		/**
		 * @return BaseAggregateCache
		**/
		protected function doAddPeer($label, CachePeer $peer)
		{
			if (isset($this->peers[$label]))
				throw new WrongArgumentException(
					'use unique names for your peers'
				);

			if ($peer->isAlive())
				$this->alive = true;
			
			$this->peers[$label]['object'] = $peer;
			$this->peers[$label]['stat'] = array();
			
			return $this;
		}

		/**
		 * low-level cache access
		**/

		public function increment($key, $value)
		{
			$label = $this->guessLabel($key);

			if ($this->peers[$label]['object']->isAlive())
				return $this->peers[$label]['object']->increment($key, $value);
			else
				$this->checkAlive();

			return null;
		}

		public function decrement($key, $value)
		{
			$label = $this->guessLabel($key);

			if ($this->peers[$label]['object']->isAlive())
				return $this->peers[$label]['object']->decrement($key, $value);
			else
				$this->checkAlive();

			return null;
		}

		public function get($key)
		{
			$label = $this->guessLabel($key);

			if ($this->peers[$label]['object']->isAlive())
				return $this->peers[$label]['object']->get($key);
			else
				$this->checkAlive();

			return null;
		}

		public function getList($indexes)
		{
			$labels = array();
			$out = array();

			foreach ($indexes as $index)
				$labels[$this->guessLabel($index)][] = $index;

			foreach ($labels as $label => $indexList) {

				/** @var CachePeer $peer **/
				$peer = $this->peers[$label]['object'];

				if ($peer->isAlive()) {
					if ($list = $peer->getList($indexList))
						$out = array_merge($out, $list);
				} else
					$this->checkAlive();
			}

			return $out;
		}

		public function delete($key)
		{
			$label = $this->guessLabel($key);

			if (!$this->peers[$label]['object']->isAlive()) {
				$this->checkAlive();
				return false;
			}

			return $this->peers[$label]['object']->delete($key);
		}

		/**
		 * @return AggregateCache
		**/
		public function clean()
		{
			foreach ($this->peers as $peer)
				$peer['object']->clean();

			$this->checkAlive();

			return parent::clean();
		}

		public function getStats()
		{
			$stats = array();

			foreach ($this->peers as $level => $peer)
				$stats[$level] = $peer['stat'];

			return $stats;
		}

		public function append($key, $data)
		{
			$label = $this->guessLabel($key);

			if ($this->peers[$label]['object']->isAlive())
				return $this->peers[$label]['object']->append($key, $data);
			else
				$this->checkAlive();

			return false;
		}

		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			$label = $this->guessLabel($key);

			if ($this->peers[$label]['object']->isAlive())
				return
					$this->peers[$label]['object']->$action(
						$key,
						$value,
						$expires
					);
			else
				$this->checkAlive();

			return false;
		}
	}
?>