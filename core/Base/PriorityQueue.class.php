<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class PriorityQueue
	{
		private $heap 			= array(0);
		private $heapSize		= 0;
		private $cmpFunction	= null;
		
		public function getLength()
		{
			return $this->heapSize;
		}
		
		public function first()
		{
			return $this->heap[1];
		}
		
		public static function create($unsortedData = array())
		{
			return new self($unsortedData);
		}
		
		public function __construct($unsortedData = array())
		{
			$this->heap = array_merge($this->heap, $unsortedData);
			
			$this->heapSize = count($this->heap) - 1;
			
			$this->buildMaxHeap();
		}
		
		/*
		function cmp($one, $two)
		{
			if ($one['d'] == $two['d'])
				return 0;
			
			return ($one['d'] < $two['d']) ? -1 : 1;
		}
		*/
		public function setCmpFunction($function)
		{
			$this->cmpFunction = $function;
			
			return $this;
		}
		
		public function parent($index)
		{
			return $index >> 1;
		}
		
		public function left($index)
		{
			return $index << 1;
		}
		
		public function right($index)
		{
			return 1 + ($index << 1);
		}
		
		public function maxHeapify($index)
		{
			$left = $this->left($index);
			$right = $this->right($index);
			
			$largest = null;
			
			$cmp = $this->cmpFunction;
			
			if ($left <= $this->heapSize && $cmp($this->heap[$left], $this->heap[$index]) == 1)
				$largest = $left;
			else
				$largest = $index;
				
			if ($right <= $this->heapSize && $cmp($this->heap[$right], $this->heap[$largest]) == 1)
				$largest = $right;
			
			if ($largest != $index) {
				$this->swapElts($index, $largest);
				
				$this->maxHeapify($largest);
			}
		}
		
		public function buildMaxHeap()
		{
			for ($i = $this->heapSize / 2; $i > 0; $i--) {
				$this->maxHeapify($i);
			}
		}
		
		public function pop()
		{
			if ($this->heapSize == 0)
				return null;
			
			$max = $this->heap[1];
			
			$this->heap[1] = $this->heap[$this->heapSize--];
			
			$this->maxHeapify(1);
			
			return $max;
		}
		
		public function push($elt)
		{
			++$this->heapSize;
			
			$this->heap[$this->heapSize] = PrimitiveInteger::SIGNED_MIN;
			
			$index = $this->heapSize;
			
			$this->heap[$index] = $elt;
			
			$cmp = $this->cmpFunction;
			
			while (
				$index > 1
				&&
					$cmp(
						$this->heap[$this->parent($index)],
						$this->heap[$index]
					) == -1
			) {
				$this->swapElts($index, $this->parent($index));
				
				$index = $this->parent($index);
			}
		}
		
		public function delete($index)
		{
			$this->heap[$index] = $this->heap[$this->heapSize];
			
			unset($this->heap[$this->heapSize]);
			
			$this->heapSize--;
			
			$this->maxHeapify($index);
		}
		
		private function swapElts($index1, $index2)
		{
			$tmp 					= $this->heap[$index2];
			$this->heap[$index2]	= $this->heap[$index1];
			$this->heap[$index1]	= $tmp;
		}
	}
