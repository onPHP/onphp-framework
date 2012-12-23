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

	// TODO: support other params
	
	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartLabelStyleNumberType
		extends BaseGoogleChartLabelStyleType
	{
		protected $name 			= 'N';
		
		private $precisionLevel		= null;
		private $type 				= null;
		private $withTrailingZero	= false;
		
		
		/**
		 * @return GoogleChartLabelStyleNumberType
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->precisionLevel = 0;
		}
		
		/**
		 * @return GoogleChartLabelStyleNumberType
		**/
		public function setPrecisionLevel($level)
		{
			$this->precisionLevel = $level;
			
			return $this;
		}
		
		public function getPrecisionLevel()
		{
			return $this->precisionLevel;
		}
		
		/**
		 * @return GoogleChartLabelStyleNumberType
		**/
		public function setType(LabelStyleType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return LabelStyleType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return GoogleChartLabelStyleNumberType
		**/
		public function setWithTrailingZero($orly = true)
		{
			$this->withTrailingZero = (true === $orly);
			
			return $this;
		}
		
		public function withTrailingZero()
		{
			return $this->withTrailingZero;
		}
		
		public function toString()
		{
			return
				$this->name
				.'*'
				.(
					$this->type
						? $this->type->toString()
						: null
						
				)
				.(
					$this->withTrailingZero
						? 'z'
						: null
						
				)
				.'*';
		}
	}
