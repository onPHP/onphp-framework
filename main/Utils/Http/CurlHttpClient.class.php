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

	final class CurlHttpClient implements HttpClient
	{
		private $handle			= null;
		private $timeout		= null;
		private $followLocation	= null;
		private $maxRedirects	= null;
		
		public function __construct()
		{
			$this->handle = curl_init();
		}
		
		public static function create()
		{
			return new self;
		}
		
		public function __destruct()
		{
			curl_close($this->handle);
		}
		
		/**
		 * @return CurlHttpClient
		 * @param $timeout in seconds
		 */
		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			return $this;
		}
		
		public function getTimeout()
		{
			return $this;
		}
		
		/**
		 * whether to follow header Location or not
		 * 
		 * @param $really boolean
		 * @return CurlHttpClient
		 */
		public function setFollowLocation($really)
		{
			Assert::isBoolean($really);
			$this->followLocation = $really;
		}
		
		public function isFollowLocation()
		{
			return $this->followLocation;
		}
		
		/**
		 * @return CurlHttpClient
		 */
		public function setMaxRedirects($maxRedirects)
		{
			$this->maxRedirects = $maxRedirects;
			return $this;
		}
		
		public function getMaxRedirects()
		{
			return $this->maxRedirects;
		}
		
		/**
		 * @return HttpResponse
		 */
		public function send(HttpRequest $request)
		{
			// TODO: support more methods
			Assert::isTrue($request->getMethod()->getId() == HttpMethod::GET);
			
			$response = CurlHttpResponse::create();
			
			$options = array(
				CURLOPT_WRITEFUNCTION => array($response, 'writeBody'),
				CURLOPT_HEADERFUNCTION => array($response, 'writeHeader'),
				CURLOPT_URL => $request->getUrl()->toString()
			);
			
			if ($this->timeout !== null)
				$options[CURLOPT_TIMEOUT] = $this->timeout;
				
			if ($this->followLocation !== null)
				$options[CURLOPT_FOLLOWLOCATION] = $this->followLocation;
				
			if ($this->maxRedirects !== null)
				$options[CURLOPT_MAXREDIRS] = $this->maxRedirects;
			
			curl_setopt_array($this->handle, $options);
			
			if (curl_exec($this->handle) === false) {
				throw new NetworkException(
					'curl error, code: '
					.curl_errno($this->handle)
					.' description: '
					.curl_error($this->handle)
				);
			}
			
			$response->setStatus(
				new HttpStatus(
					curl_getinfo(
						$this->handle, 
						CURLINFO_HTTP_CODE
					)
				)
			);
			
			return $response;
		}
	}
?>