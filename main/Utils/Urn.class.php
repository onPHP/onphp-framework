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
/* $Id$ */

	class Urn extends GenericUri
	{
		protected $schemeSpecificPart	= null;
		
		protected $knownSubSchemes	= array(
			'urn'		=> 'Urn',
			'mailto'	=> 'Urn',
			'news'		=> 'Urn',
			'isbn'		=> 'Urn',
			'tel'		=> 'Urn',
			'fax'		=> 'Urn',
			'wtai'		=> 'Urn',
		);
		
		/**
		 * @return Urn
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getKnownSubSchemes()
		{
			return $this->knownSubSchemes;
		}
		
		/**
		 * @return Urn
		**/
		public function setSchemeSpecificPart($schemeSpecificPart)
		{
			$this->schemeSpecificPart = $schemeSpecificPart;
			
			return $this;
		}
		
		public function getSchemeSpecificPart()
		{
			return $this->schemeSpecificPart;
		}
		
		public function setUserInfo($userInfo)
		{
			throw new UnsupportedMethodException();
		}
		
		public function setHost($host)
		{
			throw new UnsupportedMethodException();
		}
		
		public function setPort($port)
		{
			throw new UnsupportedMethodException();
		}
		
		public function setPath($path)
		{
			throw new UnsupportedMethodException();
		}
		
		public function setQuery($query)
		{
			throw new UnsupportedMethodException();
		}
		
		final protected function getSchemeHierPattern(
			$schemePattern, $hierPattern
		)
		{
			return parent::getSchemeHierPattern($schemePattern, $hierPattern);
		}
		
		final protected function getHierPattern()
		{
			return '([^#]+)';
			#       ^1
		}
		
		final protected function getQueryFragmentPattern()
		{
			return '(#(.*))?';
			#       ^2^3
		}
		
		/**
		 * @return Urn
		**/
		final protected function applyPatternMatches($matches)
		{
			if (!empty($matches[1]))
				$this->setSchemeSpecificPart($matches[1]);
				
			if (!empty($matches[2]))
				$this->setFragment($matches[3]);
			
			return $this;
		}
	}
?>