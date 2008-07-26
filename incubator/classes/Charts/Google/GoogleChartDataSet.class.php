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

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartDataSet
	{
		private $data = array();
		
		/**
		 * @return GoogleChartDataSet
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartDataSet
		**/
		public function setData(array $data)
		{
			$this->data = $data;
			
			return $this;
		}
		
		public function getData()
		{
			return $this->data;
		}
		
		/**
		 * @return GoogleChartDataSet
		**/
		public function addElement($element)
		{
			$this->data[] = $element;
			
			return $this;
		}
		
		public function getSize()
		{
			return count($this->data);
		}
	}
?>