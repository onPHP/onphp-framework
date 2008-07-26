<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	final class GoogleChartLabel extends BaseGoogleChartParameter
	{
		protected $name = 'chl';
		
		private $labels = array();
		
		/**
		 * @return GoogleChartLabel
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartLabel
		**/
		public function addLabel($label)
		{
			$this->labels[] = $label;
			
			return $this;
		}
		
		public function toQueryString()
		{
			$labels = implode('|', $this->labels);
			
			return "{$this->name}={$labels}";
		}
	}
?>