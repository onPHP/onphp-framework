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
/* $Id$ */

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartLine extends GoogleChartPiece
	{
		/**
		 * @return GoogleChartLine
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartLine
		**/
		public function setValue(/* GoogleChartDataSet */ $value)
		{
			return parent::setValue($value);
		}
	}
?>