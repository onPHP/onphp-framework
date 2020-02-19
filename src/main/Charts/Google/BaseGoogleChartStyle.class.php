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

	/**
	 * @ingroup GoogleChart
	**/
	abstract class BaseGoogleChartStyle extends BaseGoogleChartParameter
	{
		protected $styles = array();
		
		/**
		 * @return BaseGoogleChartStyle
		**/
		public function addStyle($style)
		{
			$this->styles[] = $style;
			
			return $this;
		}
		
		public function hasStyles()
		{
			return !empty($this->styles);
		}
	}
?>