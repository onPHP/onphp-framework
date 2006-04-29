<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Message extends IdentifiableObject implements DAOConnected
	{
		private $name		= null;
		private $nickname	= null;
		private $content	= null;
		private $posted		= null;
		
		public static function dao()
		{
			return Singleton::getInstance('MessageDAO');
		}
		
		public static function create()
		{
			return new Message();
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getNickname()
		{
			return $this->nickname;
		}
		
		public function setNickname($nickname)
		{
			$this->nickname = $nickname;
			
			return $this;
		}
		
		public function getContent()
		{
			return $this->content;
		}
		
		public function setContent($content)
		{
			$this->content = $content;
			
			return $this;
		}
		
		public function getPosted()
		{
			return $this->posted;
		}
		
		public function setPosted(Timestamp $posted)
		{
			$this->posted = $posted;
			
			return $this;
		}
	}
?>