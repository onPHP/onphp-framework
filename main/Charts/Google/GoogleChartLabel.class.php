<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Denis M. Gabaidulin                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup GoogleChart
	**/
	namespace Onphp;

	class GoogleChartLabel extends BaseGoogleChartParameter
	{
		protected $name = 'chl';
		
		protected $labels = array();
		
		/**
		 * @return \Onphp\GoogleChartLabel
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\GoogleChartLabel
		**/
		public function addLabel($label)
		{
			$this->labels[] = $label;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\GoogleChartLabel
		**/
		public function setLabels($labels)
		{
			$this->labels = $labels;
			
			return $this;
		}
		
		public function getCount()
		{
			return count($this->labels);
		}
		
		public function toString()
		{
			$labels = implode('|', $this->labels);
			
			return $this->name.'='.$labels;
		}
	}
?>