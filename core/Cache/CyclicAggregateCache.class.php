<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * One more Aggregate cache.
	 *
	 * @ingroup Cache
	**/
	namespace Onphp;

	final class CyclicAggregateCache extends BaseAggregateCache
	{
		const DEFAULT_SUMMARY_WEIGHT = 1000;
		
		private $summaryWeight = self::DEFAULT_SUMMARY_WEIGHT;
		private $sorted = false;

		/**
		 * @return \Onphp\CyclicAggregateCache
		**/
		public static function create()
		{
			return new self();
		}

		public function setSummaryWeight($weight)
		{
			Assert::isPositiveInteger($weight);
			
			$this->summaryWeight = $weight;
			$this->sorted = false;
			
			return $this;
		}

		public function addPeer($label, CachePeer $peer, $mountPoint)
		{
			Assert::isLesserOrEqual($mountPoint, $this->summaryWeight);
			Assert::isGreaterOrEqual($mountPoint, 0);

			$this->doAddPeer($label, $peer);

			$this->peers[$label]['mountPoint'] = $mountPoint;
			$this->sorted = false;
			
			return $this;
		}

		protected function guessLabel($key)
		{
			if (!$this->sorted)
				$this->sortPeers();

			$point = hexdec(substr(sha1($key), 0, 5)) % $this->summaryWeight;

			$firstPeer = reset($this->peers);

			while ($peer = current($this->peers)) {
				
				if ($point <= $peer['mountPoint'])
					return key($this->peers);

				next($this->peers);
			}

			if ($point <= ($firstPeer['mountPoint'] + $this->summaryWeight)) {
				reset($this->peers);
				
				return key($this->peers);
			}

			Assert::isUnreachable();
		}

		private function sortPeers()
		{
			uasort($this->peers, array('self', 'comparePeers'));
			
			$this->sorted = true;
			
			return $this;
		}

		private static function comparePeers(array $first, array $second)
		{
			if ($first['mountPoint'] == $second['mountPoint'])
				return 0;

			 return
				($first['mountPoint'] < $second['mountPoint']) ? -1 : 1;
		}
	}
?>