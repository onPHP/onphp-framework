<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class CurlHttpResponse implements HttpResponse 
	{
		private $headerParser	= null;
		private $body			= null;
		private $status			= null;
		
		public function __construct()
		{
			$this->headerParser = HeaderParser::create();
		}
		
		/**
		 * @return CurlHttpResponse
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * internal use only, callback for curl
		**/
		public function writeHeader($resource, $line)
		{
			$this->headerParser->doLine($line);
			return strlen($line);
		}
		
		/**
		 * internal use only, callback for curl
		**/
		public function writeBody($resource, $body)
		{
			$this->body .= $body;
			return strlen($body);
		}
		
		public function setStatus(HttpStatus $status)
		{
			$this->status = $status;
			return $this;
		}
		
		public function getStatus()
		{
			return $this->status;
		}
		
		public function getReasonPhrase()
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * @return array
		**/
		public function getHeaders()
		{
			return $this->headerParser->getHeaders();
		}
		
		public function hasHeader($name)
		{
			return $this->headerParser->hasHeader($name);
		}
		
		public function getHeader($name)
		{
			return $this->headerParser->getHeader($name);
		}
		
		public function getBody()
		{
			return $this->body;
		}
	}
?>