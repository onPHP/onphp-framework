<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class Dimension
	{
		// what is id?
		private $id			= null;
		
		private $time		= false;
		private $measure	= false;
		
		private $projection	= null;	// use memberSelections for measure?
		
		// unimplemented:
		private $levels						= array(); // TODO: just levels, memberSelections too?
		private $cubeDimentionAssociations	= array(); // what cubes include this dimension
		
		private $hierarchies	= array();
		private $displayDefault	= null;
		
		public function __construct($id)
		{
			$this->id = $id;
		}
		
		/**
		 * @return Dimension
		**/
		public static function create($id)
		{
			return new self($id);
		}
		
		/**
		 * @return Dimension
		**/
		public function setTime($isTime)
		{
			Assert::isBoolean($isTime);
			
			Assert::isFalse($this->measure && $isTime);
			
			$this->time = $isTime;
			
			return $this;
		}
		
		public function isTime()
		{
			return $this->time;
		}
		
		/**
		 * @return Dimension
		**/
		public function setMeasure($isMeasure)
		{
			Assert::isBoolean($isMeasure);
			
			Assert::isFalse($this->time && $isMeasure);
			
			$this->measure = $isMeasure;
			
			return $this;
		}
		
		public function isMeasure()
		{
			return $this->measure;
		}
		
		/**
		 * @return Hierarchy
		**/
		public function createHierarchy($type, $default = null)
		{
			// measure cannot have hierarchy?
			Assert::isFalse($this->measure);
			
			if ($default === null) {
				if (!$this->displayDefault)
					$default = true;
			} else
				Assert::isBoolean($default);
			
			$result = Hierarchy::createByType($type, $this);
			
			$this->hierarchies[] = $result;
			
			if ($default)
				$this->displayDefault = $result;
			
			return $result;
		}
		
		// TODO: projection name depends only on this id?
		public function setProjection(ObjectProjection $projection)
		{
			Assert::isTrue($this->measure);
			
			$this->projection = $projection;
			
			return $this;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function getProjection()
		{
			return $this->projection;
		}
	}
?>