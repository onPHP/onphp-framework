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

	class Url extends GenericUri
	{
		protected $knownSubSchemes	= array(
			'http'		=> 'HttpUrl',
			'https'		=> 'HttpUrl',
			'ftp'		=> 'Url',
			'nntp'		=> 'Url',
			'telnet'	=> 'Url',
			'gopher'	=> 'Url',
			'wais'		=> 'Url',
			'file'		=> 'Url',
			'prospero'	=> 'Url',
		);
		
		/**
		 * @return Url
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getKnownSubSchemes()
		{
			return $this->knownSubSchemes;
		}
		
		final protected function getSchemeHierPattern(
			$schemePattern, $hierPattern
		)
		{
			return "($schemePattern?$hierPattern)?";
		}
		
		final protected function getHierPattern()
		{
			return parent::getHierPattern();
		}
		
		final protected function getQueryFragmentPattern()
		{
			return parent::getQueryFragmentPattern();
		}
	}
?>