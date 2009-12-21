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
		private $heap = array(0);
		private $heapSize = 0;
		
		public function first()
		{
			return $this->heap[1];
		}
		
		public function last()
		{
			return $this->heap[$this->heapSize];
		}
		
		public static function create($unsortedData)
		{
			return new self($unsortedData);
		}
		
		public function __construct($unsortedData)
		{
			$this->heap = array_merge($this->heap, $unsortedData);
			
			$this->heapSize = count($this->heap) - 1;
			
			$this->buildMaxHeap();
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
			
			if ($left <= $this->heapSize && $this->heap[$left] > $this->heap[$index])
				$largest = $left;
			else
				$largest = $index;
				
			if ($right <= $this->heapSize && $this->heap[$right] > $this->heap[$largest])
				$largest = $right;
			
			if ($largest != $index) {
				$tmp = $this->heap[$largest];
				$this->heap[$largest] = $this->heap[$index];
				$this->heap[$index] = $tmp;
				
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
			
			while (
				$index > 1
				&& $this->heap[$this->parent($index)] < $this->heap[$index]
			) {
				$tmp = $this->heap[$index];
				$this->heap[$index] = $this->heap[$this->parent($index)];
				$this->heap[$this->parent($index)] = $tmp;
				
				$index = $this->parent($index);
			}
		}
	}
?>