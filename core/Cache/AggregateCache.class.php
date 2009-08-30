<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * A wrapper to multiple cache for workload
	 * distribution using CachePeer childs.
	 * 
	 * @ingroup Cache
	**/
	class AggregateCache extends SelectivePeer
	{
		const LEVEL_ULTRAHIGH	= 0xFFFF;
		const LEVEL_HIGH		= 0xC000;
		const LEVEL_NORMAL		= 0x8000;
		const LEVEL_LOW			= 0x4000;
		const LEVEL_VERYLOW		= 0x0001;

		private $peers	= array();
		private $levels	= array();

		public static function create()
		{
			return new self;
		}

		public function addPeer(
			$label, CachePeer $peer, $level = self::LEVEL_NORMAL
		)
		{
			if (isset($this->peers[$label]))
				throw new DuplicateObjectException(
					'use unique names for your peers'
				);
			
			if ($peer->isAlive()) {
				$this->peers[$label]['object'] = $peer;
				$this->peers[$label]['level'] = $level;
				$this->peers[$label]['stat'] = array();
				$this->alive = true;
			}
			
			return $this;
		}
		
		public function dropPeer($label)
		{
			if (!isset($this->peers[$label]))
				throw new MissingElementException(
					"there is no peer with '{$label}' label"
				);

			unset($this->peer[$label]);
			
			return $this;
		}
		
		public function setClassLevel($class, $level)
		{
			$this->levels[$class] = $level;
			
			return $this;
		}

		public function checkAlive()
		{
			$this->alive = false;
			
			foreach ($this->peers as $label => &$peer)
				if ($peer['object']->isAlive())
					$this->alive = true;
				else
					unset($this->peers[$label]);

			return $this->alive;
		}

		/**
		 * low-level cache access
		**/

		public function get($key)
		{
			$label = $this->guessLabel($key);
			
			if ($this->peers[$label]['object']->isAlive())
				return $this->peers[$label]['object']->get($key);
			else
				$this->checkAlive();
			
			return null;			
		}
		
		public function add($key, &$value, $expires = Cache::EXPIRES_MINIMUM)
		{
			$label = $this->guessLabel($key);
			
			if ($this->peers[$label]['object']->isAlive())
				return
					$this->peers[$label]['object']->add(
						$key,
						$value,
						$expires
					);
			else
				$this->checkAlive();
			
			return false;			
		}

		public function replace($key, &$value, $expires = Cache::EXPIRES_MINIMUM)
		{
			$label = $this->guessLabel($key);
			
			if ($this->peers[$label]['object']->isAlive())
				return
					$this->peers[$label]['object']->replace(
						$key,
						$value,
						$expires
					);
			else
				$this->checkAlive();
			
			return false;			
		}

		public function set($key, &$value, $expires = Cache::EXPIRES_MINIMUM)
		{
			$label = $this->guessLabel($key);
			
			if ($this->peers[$label]['object']->isAlive())
				return
					$this->peers[$label]['object']->set(
						$key,
						$value,
						$expires
					);
			else
				$this->checkAlive();
			
			return false;			
		}
		
		public function delete($key)
		{
			$label = $this->guessLabel($key);
			
			if (!$this->peers[$label]['object']->isAlive()) {
				$this->checkAlive();
				return false;
			}

			return
				$this->peers[$label]['object']->
					delete($key);
		}

		public function clean()
		{
			foreach ($this->peers as &$peer)
				$peer['object']->clean();

			$this->checkAlive();

			return $this;
		}

		public function getStats()
		{
			$stats = array();

			foreach ($this->peers as $level => &$peer)
				$stats[$level] = $peer['stat'];

			return $stats;
		}

		protected function store(
			$action, $key, &$value, $expires = Cache::EXPIRES_MINIMUM
		)
		{
			throw new UnsupportedMethodException();
		}

		/**
		 * brain
		**/
		private function guessLabel($key)
		{
			$class = $this->getClassName();

			if (isset($this->levels[$class]))
				$classLevel = $this->levels[$class];
			else
				$classLevel = self::LEVEL_NORMAL;
			
			// init by $key, randomness will be restored later
			mt_srand(hexdec(substr(md5($key), 3, 7)));

			$zeroDistances = array();
			$weights = array();

			foreach ($this->peers as $label => $peer) {
				$distance = abs($classLevel - $peer['level']);

				if (!$distance)
					$zeroDistances[] = $label;
				else
					$weights[$peer['level']] = 1 / pow($distance, 2); // BOVM
			}
			
			if (count($zeroDistances)) {

				$selectedLabel =
					$zeroDistances[mt_rand(0, count($zeroDistances) - 1)];

			} else {

				// weighted random level selection
				$sum = mt_rand() * array_sum($weights) / mt_getrandmax();
				$peerLevel = null;
				foreach ($weights as $level => $weight) {
					if ($sum <= $weight) {
						$peerLevel = $level;
						break;
					} else
						$sum -= $weight;
				}

				$selectedPeers = array();
				foreach ($this->peers as $label => $peer) {
					if ($peer['level'] == $peerLevel)
						$selectedPeers[] = $label;
				}

				$selectedLabel = $selectedPeers[mt_rand(0, count($selectedPeers) - 1)];
			}

			if (isset($this->peers[$selectedLabel]['stat'][$class]))
				++$this->peers[$selectedLabel]['stat'][$class];
			else
				$this->peers[$selectedLabel]['stat'][$class] = 1;
			
			// restore randomness
			mt_srand(
				(int) (
					(int) (microtime(true) << 2)
					* (rand(time() / 2, time()) >> 2)
				)
			);
			
			return $selectedLabel;
		}
	}
?>