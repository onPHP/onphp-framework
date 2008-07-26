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
	final class GoogleChartDataTextEncoding
		extends BaseGoogleChartDataEncoding
		implements GoogleChartDataEncoding
	{
		protected $name = 't:';
		protected $delimiter = ',';
		
		/**
		 * @return GoogleChartDataTextEncoding
		**/
		public static function create()
		{
			return new self;
		}
		
		public function encode(GoogleChartDataSet $set)
		{
			return implode($this->delimiter, $set->getData());
		}
		
		public function toQueryString()
		{
			return $this->name;
		}
	}
?>