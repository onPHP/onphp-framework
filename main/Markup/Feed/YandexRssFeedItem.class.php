<?php
/***************************************************************************
 *   Copyright (C) 2010 by Alexandr S. Krotov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Feed
	**/
	namespace Onphp;

	final class YandexRssFeedItem extends FeedItem
	{
		private $fullText	= null;
		
		/**
		 * @return \Onphp\YandexRssFeedItem
		**/
		public static function create($title)
		{
			return new self($title);
		}
		
		public function getFullText()
		{
			return $this->fullText;
		}
		
		/**
		 * @return \Onphp\YandexRssFeedItem
		**/
		public function setFullText($fullText)
		{
			$this->fullText = $fullText;
			
			return $this;
		}
	}
?>