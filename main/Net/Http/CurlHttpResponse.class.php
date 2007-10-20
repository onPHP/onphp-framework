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

	/**
	 * @ingroup Http
	**/
	final class CurlHttpResponse implements HttpResponse
	{
		private $headerParser		= null;
		private $body				= null;
		private $status				= null;
		private $maxFileSize		= null;
		private $currentFileSize	= null;
		
		public function __construct()
		{
			$this->headerParser = HeaderParser::create();
			$this->currentFileSize = 0;
		}
		
		/**
		 * @return CurlHttpResponse
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * internal use only, callback for curl client
		**/
		public function writeHeader($resource, $line)
		{
			$this->headerParser->doLine($line);
			
			if (
				$this->maxFileSize !== null
				&& $this->headerParser->hasHeader('Content-Length')
				&& $this->headerParser->getHeader('Content-Length')
					> $this->maxFileSize
			)
				return -1; // see http://curl.haxx.se/libcurl/c/curl_easy_setopt.html CURLOPT_HEADERFUNCTION
			else
				return strlen($line);
		}
		
		/**
		 * internal use only, callback for curl client
		**/
		public function writeBody($resource, $body)
		{
			$this->body .= $body;
			$obtained = strlen($body);
			
			if (
				$this->maxFileSize !== null
				&& $this->currentFileSize + $obtained > $this->maxFileSize
			) {
				return -1;
			} else {
				$this->currentFileSize += $obtained;
				return $obtained;
			}
		}
		
		/**
		 * internal use only for curl client
		 * @return CurlHttpResponse
		**/
		public function setMaxFileSize($maxFileSize)
		{
			$this->maxFileSize = $maxFileSize;
			return $this;
		}
		
		/**
		 * @return CurlHttpResponse
		**/
		public function setStatus(HttpStatus $status)
		{
			$this->status = $status;
			return $this;
		}
		
		/**
		 * @return HttpStatus
		**/
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