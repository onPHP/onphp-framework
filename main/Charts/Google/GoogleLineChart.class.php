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
	class GoogleLineChart extends GoogleChart
	{
		protected $axesCollection 	= null;
		protected $style 			= null;
		protected $labelStyle 		= null;
		
		/**
		 * @return GoogleLineChart
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			parent::__construct();
			
			$this->type =
				new GoogleChartType(GoogleChartType::LINE);
			
			$this->color = GoogleChartColor::create();
			
			$this->legend =
				GoogleChartLegend::create()->
				setPosition(
					GoogleChartLegendPositionType::create(
						GoogleChartLegendPositionType::BOTTOM
					)
				);
			
			$this->data =
				GoogleChartData::create()->
				setEncoding(GoogleChartDataTextEncoding::create())->
				setDataScaling();
			
			$this->axesCollection = GoogleChartAxisCollection::create();
			
			$this->style = GoogleChartLineStyle::create();
			
			$this->labelStyle = GoogleChartLabelStyle::create();
		}
		
		/**
		 * @return GoogleLineChart
		**/
		public function addLine(GoogleChartLine $line)
		{
			$this->color->addColor($line->getColor());
			$this->legend->addItem($line->getTitle());
			$this->data->addDataSet($line->getValue());
			
			if ($style = $line->getStyle())
				$this->style->addStyle($style);
			
			if ($labelStyle = $line->getLabelStyle())
				$this->labelStyle->addStyle($labelStyle);
			
			return $this;
		}
		
		/**
		 * @return GoogleLineChart
		**/
		public function setLegendPosition(GoogleChartLegendPositionType $type)
		{
			$this->legend->setPosition($type);
			
			return $this;
		}
		
		/**
		 * @return GoogleLineChart
		**/
		public function addAxis(GoogleChartAxis $axis)
		{
			$this->axesCollection->addAxis($axis);
			
			return $this;
		}
		
		public function toString()
		{
			$string = parent::toString();
			
			$string .= '&'.$this->axesCollection->toString();
			
			if ($this->style->hasStyles())
				$string .= '&'.$this->style->toString();
			
			if ($this->labelStyle->hasStyles())
				$string .= '&'.$this->labelStyle->toString();
			
			return $string;
		}
	}
?>