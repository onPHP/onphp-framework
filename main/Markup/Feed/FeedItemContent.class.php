<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
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

	final class FeedItemContent
	{
		private $type = null;
		private $body = null;
		
		/**
		 * @return \Onphp\FeedItemContent
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\FeedItemContentType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return \Onphp\FeedItemContent
		**/
		public function setType(FeedItemContentType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getBody()
		{
			return $this->body;
		}
		
		/**
		 * @return \Onphp\FeedItemContent
		**/
		public function setBody($body)
		{
			$this->body = $body;
			
			return $this;
		}
	}
?>