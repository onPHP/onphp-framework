<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Anton E. Lebedevich                        *
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
	class CurlHttpClient implements HttpClient
	{
		private $timeout		= null;
		private $followLocation	= null;
		private $maxRedirects	= null;
		private $maxFileSize	= null;
		private $noBody			= null;
		
		/**
		 * @return CurlHttpClient
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CurlHttpClient
		 * @param $timeout in seconds
		**/
		public function setTimeout($timeout)
		{
			$this->timeout = $timeout;
			return $this;
		}
		
		public function getTimeout()
		{
			return $this->timeout;
		}
		
		/**
		 * whether to follow header Location or not
		 * 
		 * @param $really boolean
		 * @return CurlHttpClient
		**/
		public function setFollowLocation($really)
		{
			Assert::isBoolean($really);
			$this->followLocation = $really;
			return $this;
		}
		
		public function isFollowLocation()
		{
			return $this->followLocation;
		}
		
		/**
		 * @param $really boolean
		 * @return CurlHttpClient
		**/
		public function setNoBody($really)
		{
			Assert::isBoolean($really);
			$this->noBody = $really;
			return $this;
		}
		
		public function hasNoBody()
		{
			return $this->noBody;
		}
		
		/**
		 * @return CurlHttpClient
		**/
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
		 * @return CurlHttpClient
		**/
		public function setMaxFileSize($maxFileSize)
		{
			$this->maxFileSize = $maxFileSize;
			return $this;
		}
		
		public function getMaxFileSize()
		{
			return $this->maxFileSize;
		}
		
		/**
		 * @return HttpResponse
		**/
		public function send(HttpRequest $request)
		{
			Assert::isTrue(
				in_array(
					$request->getMethod()->getId(),
					array(HttpMethod::GET, HttpMethod::POST)
				)
			);
			
			$handle = curl_init();
			
			$response = CurlHttpResponse::create()->
				setMaxFileSize($this->maxFileSize);
			
			$options = $this->makeOptions($request, $response);
			
			curl_setopt_array($handle, $options);
			
			if (curl_exec($handle) === false) {
				throw new NetworkException(
					'curl error, code: '
					.curl_errno($handle)
					.' description: '
					.curl_error($handle)
				);
			}
			
			$response->setStatus(
				new HttpStatus(
					curl_getinfo($handle, CURLINFO_HTTP_CODE)
				)
			);
			
			curl_close($handle);
			
			return $response;
		}
		
		protected function makeOptions(HttpRequest $request, CurlHttpResponse $response)
		{
			$options = array(
				CURLOPT_WRITEFUNCTION => array($response, 'writeBody'),
				CURLOPT_HEADERFUNCTION => array($response, 'writeHeader'),
				CURLOPT_URL => $request->getUrl()->toString(),
				CURLOPT_USERAGENT => 'onPHP::'.__CLASS__
			);
			
			if ($this->timeout !== null)
				$options[CURLOPT_TIMEOUT] = $this->timeout;
			
			if ($this->followLocation !== null)
				$options[CURLOPT_FOLLOWLOCATION] = $this->followLocation;
			
			if ($this->maxRedirects !== null)
				$options[CURLOPT_MAXREDIRS] = $this->maxRedirects;
			
			if ($request->getMethod()->getId() == HttpMethod::GET) {
				$options[CURLOPT_HTTPGET] = true;
				
				if ($request->getGet()) {
					$options[CURLOPT_URL] .=
						'?'.$this->argumentsToString($request->getGet());
				}
			} else {
				$options[CURLOPT_POST] = true;
				$options[CURLOPT_POSTFIELDS] =
					$this->argumentsToString($request->getPost());
			}
			
			if ($this->noBody !== null)
				$options[CURLOPT_NOBODY] = $this->noBody;
			
			return $options;
		}
		
		private function argumentsToString($array)
		{
			Assert::isArray($array);
			$result = array();
			
			foreach ($array as $key => $value) {
				$result[] = $key.'='.urlencode($value);
			}
			
			return implode('&', $result);
		}
	}
?>