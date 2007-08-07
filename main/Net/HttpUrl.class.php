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

	/**
	 * @ingroup Net
	**/
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
		
		public function ensureAbsolute()
		{
			$this->fixMistakenPath();
			
			if (!$this->scheme && !$this->getAuthority()) {
				$this->scheme = 'http';
				
				$segments = explode('/', $this->path);
				
				if (!empty($segments[0])) {
					// localhost/anything becomes http://localhost/anything
					
					$this->setAuthority(array_shift($segments));
					
					$this->setPath('/'.implode('/', $segments));
				}
			}
			
			$this->fixAuthorityFromPath();
			
			return $this;
		}
		
		public function isValid()
		{
			if (!in_array($this->scheme, array('http', 'https')))
				return false;
			
			if (
				$this->port
				&& !in_array($this->port, array(80, 443))
				&& $this->port < 1024
			)
				return false;
			
			return parent::isValid();
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