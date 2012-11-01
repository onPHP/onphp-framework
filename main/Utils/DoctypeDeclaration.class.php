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
	 * Inline DTD:
	 * <!DOCTYPE greeting [
	 *  <!ELEMENT greeting (#PCDATA)>
	 * ]>
	 * 
	 * System DTD:
	 * <!DOCTYPE greeting SYSTEM "hello.dtd">
	 * 
	 * Public DTD:
	 * <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN"
	 *  "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
	 * or
	 * <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
	 * 
	 * @ingroup Utils
	**/
	namespace Onphp;

	class DoctypeDeclaration
	{
		const SPACER_MASK			= '[ \r\n\t]';
		const ID_FIRST_CHAR_MASK	= '[A-Za-z]';
		const ID_CHAR_MASK			= '[-_:.A-Za-z0-9]';
		
		protected $fpi			= null;
		
		private $rootElement	= null;
		
		private $inline			= false;
		private $declarations	= null;	// unparsed
		
		private $public			= false;
		
		private $uri			= null;
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setRootElement($rootElement)
		{
			$this->rootElement = $rootElement;
			
			return $this;
		}
		
		public function getRootElement()
		{
			return $this->rootElement;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setInline($isInline)
		{
			Assert::isBoolean($isInline);
			
			$this->inline = $isInline;
			$this->public = false;
			
			return $this;
		}
		
		public function isInline()
		{
			return $this->inline;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setPublic($isPublic)
		{
			Assert::isBoolean($isPublic);
			
			$this->public = $isPublic;
			$this->inline = false;
			
			return $this;
		}
		
		public function isPublic()
		{
			return $this->public;
		}
		
		public function isSystem()
		{
			return !$this->public;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setDeclarations($declarations)
		{
			$this->declarations = $declarations;
			
			return $this;
		}
		
		public function getDeclarations()
		{
			return $this->declarations;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setFpi($fpi)
		{
			$this->fpi = $fpi;
			
			return $this;
		}
		
		public function getFpi()
		{
			return $this->fpi;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		**/
		public function setUri($uri)
		{
			$this->uri = $uri;
			
			return $this;
		}
		
		public function getUri()
		{
			return $this->uri;
		}
		
		/**
		 * @return \Onphp\DoctypeDeclaration
		 * 
		 * sample argument: html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
		**/
		public function parse($string)
		{
			$matches = array();
			
			if (
				!preg_match(
					'~^('.self::ID_FIRST_CHAR_MASK.self::ID_CHAR_MASK.'*)'
					.self::SPACER_MASK.'+(.*)$~s',
					$string, $matches
				)
			) {
				return null;
			}
			
			$this->rootElement = $matches[1];
			$remainigString = $matches[2];
			
			if (
				preg_match(
					'~^PUBLIC'.self::SPACER_MASK.'+"(.+?)"'
					.'('.self::SPACER_MASK.'*"(.+)")?$~is',
					$remainigString, $matches
				)
			) {
				$this->public = true;
				
				$this->inline = false;
				$this->declarations = null;
				
				$this->setFpi($matches[1]);
				
				if (isset($matches[3]))
					$this->uri = $matches[3];
					
			} elseif (
				preg_match(
					'~^SYSTEM'.self::SPACER_MASK.'+"(.+?)"$~is',
					$remainigString, $matches
				)
			) {
				$this->public = false;
				
				$this->inline = false;
				$this->declarations = null;
				
				$this->setFpi(null);
				$this->uri = $matches[1];
				
			} else {
				$this->public = false;
				
				$this->inline = true;
				$this->declarations = $remainigString;
				
				$this->setFpi(null);
				$this->uri = null;
			}
			
			return $this;
		}
		
		public function toString()
		{
			if ($this->inline)
				return $this->rootElement.' '.$this->declarations;
				
			elseif ($this->public)
				return
					$this->rootElement.' PUBLIC "'.$this->getFpi().'"'
					.(
						$this->uri
						? ' "'.$this->uri.'"'
						: null
					);
			else
				return
					$this->rootElement.' SYSTEM "'.$this->getFpi().'"';
		}
	}
?>