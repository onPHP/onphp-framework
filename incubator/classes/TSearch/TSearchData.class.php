<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: TSearchData.class.php 203 2008-11-27 10:03:43Z ssserj $ */

	final class TSearchData implements Stringable
	{
		protected $delimiter= ' ';
		protected $weights	= array();
		protected $filter	= null;
		
		/**
		 * @return TSearchData
		**/
		public static function create()
		{
			return new self();
		}
		
		public function getWeights()
		{
			return $this->weights;
		}
		
		/**
		 * @return TSearchData
		**/
		public function setFilter(Filtrator $filter)
		{
			$this->filter = $filter;
			
			return $this;
		}
		
		public function resetFilter()
		{
			return $this->filter = null;
		}
		
		/**
		 * @return TSearchData
		**/
		public function setWeightA(array $data)
		{
			$this->setWeight(TSearchConfigurator::WEIGHT_A, $data);
			
			return $this;
		}
		
		public function getWeightA()
		{
			return $this->weights[TSearchConfigurator::WEIGHT_A];
		}
		
		/**
		 * @return TSearchData
		**/
		public function resetWeightA()
		{
			$this->weights[TSearchConfigurator::WEIGHT_A] = array();

			return $this;
		}
		
		/**
		 * @return TSearchData
		**/
		public function addWeightA($data)
		{
			$this->addWeight(TSearchConfigurator::WEIGHT_A, $data);

			return $this;
		}
		
		public function toStringWeightA()
		{
			return $this->toStringByWeight(TSearchConfigurator::WEIGHT_A);
		}
		
		/**
		 * @return TSearchData
		**/
		public function setWeightB(array $data)
		{
			$this->setWeight(TSearchConfigurator::WEIGHT_B, $data);

			return $this;
		}
		
		public function getWeightB()
		{
			return $this->weights[TSearchConfigurator::WEIGHT_B];
		}
		
		/**
		 * @return TSearchData
		**/
		public function resetWeightB()
		{
			$this->weights[TSearchConfigurator::WEIGHT_B] = array();

			return $this;
		}
		
		/**
		 * @return TSearchData
		**/
		public function addWeightB($data)
		{
			$this->addWeight(TSearchConfigurator::WEIGHT_B, $data);

			return $this;
		}
		
		public function toStringWeightB()
		{
			return $this->toStringByWeight(TSearchConfigurator::WEIGHT_B);
		}
		
		/**
		 * @return TSearchData
		**/
		public function setWeightC(array $data)
		{
			$this->setWeight(TSearchConfigurator::WEIGHT_C, $data);

			return $this;
		}
		
		public function getWeightC()
		{
			return $this->weights[TSearchConfigurator::WEIGHT_C];
		}
		
		/**
		 * @return TSearchData
		**/
		public function resetWeightC()
		{
			$this->weights[TSearchConfigurator::WEIGHT_C] = array();

			return $this;
		}
		
		/**
		 * @return TSearchData
		**/
		public function addWeightC($data)
		{
			$this->addWeight(TSearchConfigurator::WEIGHT_C, $data);

			return $this;
		}
		
		public function toStringWeightC()
		{
			return $this->toStringByWeight(TSearchConfigurator::WEIGHT_C);
		}
		
		public function setWeightD(array $data)
		{
			$this->setWeight(TSearchConfigurator::WEIGHT_D, $data);

			return $this;
		}
		
		public function getWeightD()
		{
			return $this->weights[TSearchConfigurator::WEIGHT_D];
		}
		
		/**
		 * @return TSearchData
		**/
		public function resetWeightD()
		{
			$this->weights[TSearchConfigurator::WEIGHT_D] = array();

			return $this;
		}
		
		/**
		 * @return TSearchData
		**/
		public function addWeightD($data)
		{
			$this->addWeight(TSearchConfigurator::WEIGHT_D, $data);

			return $this;
		}
		
		public function toStringWeightD()
		{
			return $this->toStringByWeight(TSearchConfigurator::WEIGHT_D);
		}
		
		public function getHash()
		{
			return sha1($this->toString());
		}
		
		public function toString()
		{
			return $this->toStringWeight($this->weights);
		}
		
		public function toStringByWeight($weight)
		{
			if (!isset($this->weights[$weight]))
				throw new WrongArgumentException("Weight '{$weight}' is not set");
			
			return $this->toStringWeight($this->weights[$weight]);
		}
		
		/**
		 * @return TSearchData
		**/
		protected function setWeight($weight, $data)
		{
			if (empty($data))
				return $this;
			
			$this->weights[$weight] = $this->process($data);
			
			return $this;
		}
		
		/**
		 * @return TSearchData
		**/
		protected function addWeight($weight, $data)
		{
			if (empty($data))
				return $this;
			
			$this->weights[$weight][] = $this->process($data);
			
			return $this;
		}
		
		protected function toStringWeight(array $data)
		{
			return implode($this->delimiter, $this->multiImplode($data));
		}
		
		protected function process($data)
		{
			if (is_array($data))
				return array_map(array($this, 'process'), $data);
			elseif (is_string($data))
				return $this->cleanupData($data);
			elseif ($data instanceof Stringable)
				return $this->cleanupData($data->toString());
			else
				throw new WrongArgumentException();
		}
		
		protected function cleanupData($data)
		{
			return
				($this->filter)
					? $this->filter->apply($data)
					: $data;
		}
		
		protected function multiImplode($arrays, &$target = array())
		{
			foreach ($arrays as $item) {
				if (is_array($item)) {
					$this->multiImplode($item, $target);
				} else {
					$target[] = $item;
				}
			}
			
			return $target;
		}
	}
?>