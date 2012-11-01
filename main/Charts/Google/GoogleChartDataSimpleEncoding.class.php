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

	/**
	 * @ingroup GoogleChart
	**/
	namespace Onphp;

	final class GoogleChartDataSimpleEncoding
		extends BaseGoogleChartDataEncoding
	{
		protected $name = 's:';
		
		private $encodingChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		private $length = null;
		
		/**
		 * @return \Onphp\GoogleChartDataSimpleEncoding
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->length = strlen($this->encodingChars);
		}
		
		public function encode(GoogleChartDataSet $set)
		{
			$encodedString = null;
			
			foreach ($set->getData() as $dataElement) {
				if ($dataElement >= 0)
					 $encodedString .=
						$this->encodingChars[
							round($this->length - 1)
							* $dataElement
							/ $this->maxValue
						];
				else
					$encodedString .= '_';
			}
			
			return $encodedString;
		}
		
		public function toString()
		{
			return $this->name;
		}
	}
?>