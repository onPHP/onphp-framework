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
	class GoogleNormalizedLineChart extends GoogleLineChart
	{
		/**
		 * @return GoogleNormalizedLineChart
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			parent::__construct();
			
			$this->data->setNormalize();
		}
	}
?>