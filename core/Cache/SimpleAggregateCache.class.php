<?php
/****************************************************************************
 *   Copyright (C) 2010 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * A wrapper like AggregateCache, but it has very simple
	 * (and fast) selective algorithm
	 * 
	 * @ingroup Cache
	**/
	final class SimpleAggregateCache extends AggregateCache
	{
		private $peerAmount	= null;
		private $labels		= null;
		
		/**
		 * @return SimpleAggregateCache
		**/
		public static function create()
		{
			return new self;
		}

		public function addPeer(
			$label, CachePeer $peer, $level = self::LEVEL_NORMAL
		)
		{
			parent::addPeer($label, $peer, $level);

			return $this->dropHelpers();
		}

		public function dropPeer($label)
		{
			parent::dropPeer($label);

			return $this->dropHelpers();
		}

		public function checkAlive()
		{
			parent::checkAlive();

			return $this->dropHelpers();
		}
		
		/**
		 * brainless ;)
		**/
		protected function guessLabel($key)
		{
			if ($this->peerAmount === null)
				$this->peerAmount = count($this->peers);

			if ($this->labels === null)
				$this->labels = array_keys($this->peers);

			Assert::isGreaterOrEqual($this->peerAmount, 1);
			
			return
				$this->labels[ord(substr($key, -1)) % $this->peerAmount];
		}

		private function dropHelpers()
		{
			$this->peerAmount	= null;
			$this->labels		= null;

			return $this;
		}
	}
