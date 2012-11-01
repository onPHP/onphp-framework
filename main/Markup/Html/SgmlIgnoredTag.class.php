<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Html
	**/
	namespace Onphp;

	final class SgmlIgnoredTag extends SgmlTag
	{
		private $cdata		= null;
		private $endMark	= null;
		
		/**
		 * @return \Onphp\SgmlIgnoredTag
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\SgmlIgnoredTag
		**/
		public static function comment()
		{
			return self::create()->setId('!--')->setEndMark('--');
		}
		
		/**
		 * @return \Onphp\SgmlIgnoredTag
		**/
		public function setCdata(Cdata $cdata)
		{
			$this->cdata = $cdata;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\Cdata
		**/
		public function getCdata()
		{
			return $this->cdata;
		}
		
		/**
		 * @return \Onphp\SgmlIgnoredTag
		**/
		public function setEndMark($endMark)
		{
			$this->endMark = $endMark;
			
			return $this;
		}
		
		public function getEndMark()
		{
			return $this->endMark;
		}
		
		public function isComment()
		{
			return $this->id == '!--';
		}
		
		public function isExternal()
		{
			return ($this->id && $this->id[0] == '?');
		}
	}
?>