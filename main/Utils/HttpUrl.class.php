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

	class HttpUrl extends Url
	{
		protected $knownSubSchemes	= array();
		
		/**
		 * @return HttpUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return HttpUrl
		**/
		public function setScheme($scheme)
		{
			if (!in_array($scheme, array('http', 'https')))
				throw new WrongArgumentException('not allowed URL scheme');
			
			parent::setScheme($scheme);
		}
		
		/**
		 * @return HttpUrl
		**/
		public function setPort($port)
		{
			if (
				!in_array($port, array(80, 443))
				&& $port < 1024
			)
				throw new SecurityException('not allowed port');
			
			parent::setPort($port);
		}
		
		protected function isValidHostName()
		{
			if (!parent::isValidHostName())
				return false;
			
			$charPattern = $this->charPattern(null);
			
			// using rfc 2396, in order to detect bad ip address ranges like
			// 666.666.666.666 which are valid hostnames in generic uri syntax
			
			$topLabelPattern = '(([a-z])|([a-z]([a-z0-9-])*[a-z0-9]))\.?';
			
			return (
				preg_match(
					"/^($charPattern*\.)?{$topLabelPattern}$/i",
					$this->host
				) == 1
			);
		}
	}
?>