<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class HttpContentType
	{
		private $mediaType	= null;
		private $parameters	= array();
		
		private $charset	= null; // reference
		
		/**
		 * @return HttpContentType
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return HttpContentType
		**/
		public function setMediaType($mediaType)
		{
			$this->mediaType = $mediaType;
			
			return $this;
		}
		
		public function getMediaType()
		{
			return $this->mediaType;
		}
		
		/**
		 * @return HttpContentType
		**/
		public function setParameter($attribute, $value)
		{
			$this->parameter[$attribute] = $value;
			
			return $this;
		}
		
		/**
		 * @return HttpContentType
		**/
		public function dropParameter($attribute)
		{
			unset($this->parameter);
			
			return $this;
		}
		
		public function hasParameter($attribute)
		{
			return isset($this->parameters[$attribute]);
		}
		
		public function getParameter($attribute)
		{
			return $this->parameters[$attribute];
		}
		
		/**
		 * @return HttpContentType
		**/
		public function setParametersList($parameters)
		{
			$this->parameters = $parameters;
			
			return $this;
		}
		
		public function getParametersList()
		{
			return $this->parameters;
		}
		
		public function getCharset()
		{
			return $this->charset;
		}
		
		/**
		 * @return HttpContentType
		**/
		public function setCharset($charset)
		{
			$this->charset = $charset;
			
			return $this;
		}
		
		/**
		 * @return HttpContentType
		 *
		 * sample argument: text/html; charset=utf-8
		**/
		public function import($string)
		{
			$this->charset = null;
			$this->parameters = array();
			
			$parts = explode(';', $string);
			$this->mediaType = trim(array_shift($parts));
			
			foreach ($parts as $parameter) {
				$parameterParts = explode('=', $parameter);
				
				$attribute = strtolower(trim($parameterParts[0]));
				
				if (isset($parameterParts[1]))
					$value = trim($parameterParts[1]);
				else
					$value = null;
				
				$this->parameters[$attribute] = $value;
				
				if ($attribute == 'charset')
					// NOTE: reference
					$this->charset = &$this->parameters[$attribute];
			}
			
			return $this;
		}
		
		public function toString()
		{
			$parts = array($this->mediaType);
			
			foreach ($this->parameters as $attribute => $value) {
				$parts[] = $attribute.'='.$value;
			}
			return implode('; ', $parts);
		}
	}
?>