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
	 * @ingroup Filters
	**/
	final class JsonDecoderFilter extends BaseFilter
	{
		private $assoc = false;
		
		/**
		 * @return JsonDecodeFilter
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return JsonDecodeFilter
		**/
		public function setAssoc($orly = true)
		{
			$this->assoc = (true === $orly);
			
			return $this;
		}
		
		public function apply($value)
		{
			return json_decode($value, $this->assoc);
		}
	}
?>