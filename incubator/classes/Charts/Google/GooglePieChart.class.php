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
	class GooglePieChart extends GoogleChart
	{
		/**
		 * @return GooglePieChart
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->type =
				new GoogleChartType(GoogleChartType::TWO_DIMENSIONAL_PIE);
			
			$this->color = GoogleChartColor::create();
			
			$this->label = GoogleChartLabel::create();
			
			$this->data =
				GoogleChartData::create()->
				addDataSet(GoogleChartDataSet::create())->
				setEncoding(GoogleChartDataTextEncoding::create());
		}
		
		/**
		 * @return GooglePieChart
		**/
		public function addPiece(GoogleChartPiece $piece)
		{
			$this->color->addColor($piece->getColor());
			$this->label->addLabel($piece->getTitle());
			$this->data->getDataSetByIndex(0)->addElement($piece->getValue());
			
			return $this;
		}
	}
?>