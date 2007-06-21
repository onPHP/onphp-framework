<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class FeedItemContent
	{
		private $type;
		private $body;
		
		/**
		 * @return FeedItemContent
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return FeedItemContent
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
		 * @return FeedItemContent
		**/
		public function setBody($body)
		{
			$this->body = $body;
			
			return $this;
		}
	}
?>
		