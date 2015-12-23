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
    /** @var array  */
    private $heap = [0];

    /** @var integer */
    private $heapSize = 0;

    /** @var null|callable */
    private $cmpFunction = null;

    /**
     * PriorityQueue constructor.
     * @param array $unsortedData
     */
    public function __construct(array $unsortedData)
    {
        $this->heap = array_merge($this->heap, $unsortedData);

        $this->heapSize = count($this->heap) - 1;

        $this->buildMaxHeap();
    }

    /**
     * @see buildMaxHeap
     */
    public function buildMaxHeap()
    {
        for ($i = $this->heapSize / 2; $i > 0; $i--) {
            $this->maxHeapify($i);
        }
    }

    /**
     * @param $index
     */
    public function maxHeapify($index)
    {
        $left = $this->left($index);
        $right = $this->right($index);

        $largest = null;

        $cmp = $this->cmpFunction;

        if ($left <= $this->heapSize && $cmp($this->heap[$left], $this->heap[$index]) == 1) {
            $largest = $left;
        } else {
            $largest = $index;
        }

        if ($right <= $this->heapSize && $cmp($this->heap[$right], $this->heap[$largest]) == 1) {
            $largest = $right;
        }

        if ($largest != $index) {
            $this->swapElts($index, $largest);

            $this->maxHeapify($largest);
        }
    }

    /**
     * @param $index
     * @return integer
     */
    public function left($index) : int
    {
        return $index << 1;
    }

    /**
     * @param $index
     * @return integer
     */
    public function right($index) : int
    {
        return 1 + ($index << 1);
    }

    /**
     * @param $index1
     * @param $index2
     */
    private function swapElts($index1, $index2)
    {
        $tmp = $this->heap[$index2];
        $this->heap[$index2] = $this->heap[$index1];
        $this->heap[$index1] = $tmp;
    }

    /**
     * @param array $unsortedData
     * @return PriorityQueue
     */
    public static function create($unsortedData = []) : PriorityQueue
    {
        return new self($unsortedData);
    }

    /**
     * @return integer
     */
    public function getLength() : int
    {
        return $this->heapSize;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        return $this->heap[1];
    }

    /**
     * @param $function
     * @return PriorityQueue
     */
    public function setCmpFunction($function) : PriorityQueue
    {
        $this->cmpFunction = $function;

        return $this;
    }

    /**
     * @return null
     */
    public function pop()
    {
        if ($this->heapSize == 0) {
            return null;
        }

        $max = $this->heap[1];

        $this->heap[1] = $this->heap[$this->heapSize--];

        $this->maxHeapify(1);

        return $max;
    }

    /**
     * @param $elt
     */
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

    /**
     * @param $index
     * @return integer
     */
    public function parent($index) : int
    {
        return $index >> 1;
    }

    /**
     * delete by index
     *
     * @param $index
     */
    public function delete($index)
    {
        $this->heap[$index] = $this->heap[$this->heapSize];

        unset($this->heap[$this->heapSize]);

        $this->heapSize--;

        $this->maxHeapify($index);
    }
}
