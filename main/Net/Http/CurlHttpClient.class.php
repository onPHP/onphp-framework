<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: CurlHttpClient.class.php 45 2009-05-08 07:41:33Z lom $ */

	/**
	 * @ingroup Http
	**/
	final class CurlHttpClient implements HttpClient
	{
		private $options		= array();
		
		private $followLocation	= null;
		private $maxFileSize	= null;
		private $noBody			= null;
		private $multiRequests = array();
		private $multiResponses = array();
		private $multiThreadOptions = array();
		
		/**
		 * @return CurlHttpClient
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CurlHttpClient
		**/
		public function setOption($key, $value)
		{
			$this->options[$key] = $value;
			
			return $this;
		}
		
		/**
		 * @return CurlHttpClient
		**/
		public function dropOption($key)
		{
			unset($this->options[$key]);
			
			return $this;
		}
		
		public function getOption($key)
		{
			if (isset($this->options[$key]))
				return $this->options[$key];
			
			throw new MissingElementException();
		}
		
		/**
		 * @param $timeout int seconds
		 * @return CurlHttpClient
		**/
		public function setTimeout($timeout)
		{
			$this->options[CURLOPT_TIMEOUT] = $timeout;
			
			return $this;
		}
		
		/**
		 * @deprecated by getOption()
		**/
		public function getTimeout()
		{
			if (isset($this->options[CURLOPT_TIMEOUT]))
				return $this->options[CURLOPT_TIMEOUT];
			
			return null;
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
			$this->options[CURLOPT_MAXREDIRS] = $maxRedirects;
			
			return $this;
		}
		
		public function getMaxRedirects()
		{
			if (isset($this->options[CURLOPT_MAXREDIRS]))
				return $this->options[CURLOPT_MAXREDIRS];
			
			return null;
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
		 * @return CurlHttpClient
		**/
		public function addRequest(HttpRequest $request, $options = array())
		{
			Assert::isArray($options);
			
			$key = $this->getRequestKey($request);
			
			if (isset($this->multiRequests[$key]))
				throw new WrongArgumentException('There is allready such alias');
			
			$this->multiRequests[$key] = $request;
			
			foreach ($options as $k => $val)
				$this->multiThreadOptions[$key][$k] = $val;
			
			return $this;
		}
		
		/**
		 * @return CurlHttpResponse
		**/
		public function getResponse(HttpRequest $request)
		{
			$key = $this->getRequestKey($request);
			
			if (!isset($this->multiResponses[$key]))
				throw new WrongArgumentException('There is no response fo this alias');
			
			return $this->multiResponses[$key];
		}
		
		/**
		 * @return HttpResponse
		**/
		public function send(HttpRequest $request)
		{
			$response = CurlHttpResponse::create()->
				setMaxFileSize($this->maxFileSize);
			
			$handle = $this->makeHandle($request, $response);
			
			if (curl_exec($handle) === false) {
				$code = curl_errno($handle);
				throw new NetworkException(
					'curl error, code: '.$code
						.' description: '.curl_error($handle),
					$code
				);
			}
			
			$this->makeResponse($handle, $response);
			
			curl_close($handle);
			
			return $response;
		}
		
		public function multiSend()
		{
			Assert::isNotEmptyArray($this->multiRequests);
			
			$handles = array();
			$mh = curl_multi_init();
			
			foreach ($this->multiRequests as $alias => $request) {
				$this->multiResponses[$alias] = new CurlHttpResponse();
				
				$handles[$alias] =
					$this->makeHandle(
						$request,
						$this->multiResponses[$alias]
					);
				
				if (isset($this->multiThreadOptions[$alias]))
					foreach ($this->multiThreadOptions[$alias] as $key => $value)
						curl_setopt($handles[$alias], $key, $value);
				
				curl_multi_add_handle($mh, $handles[$alias]);
			}
			
			$running = null;
			do {
				curl_multi_exec($mh, $running);
			} while ($running > 0);
			
			foreach ($this->multiResponses as $alias => $response) {
				$this->makeResponse($handles[$alias], $response);
				curl_multi_remove_handle($mh, $handles[$alias]);
				curl_close($handles[$alias]);
			}
			
			curl_multi_close($mh);
			
			return true;
		}
		
		protected function getRequestKey(HttpRequest $request)
		{
			return md5(serialize($request));
		}
		
		protected function makeHandle(HttpRequest $request, CurlHttpResponse $response)
		{
			$handle = curl_init();
			Assert::isNotNull($request->getMethod());
			
			$options = array(
				CURLOPT_WRITEFUNCTION => array($response, 'writeBody'),
				CURLOPT_HEADERFUNCTION => array($response, 'writeHeader'),
				CURLOPT_URL => $request->getUrl()->toString(),
				CURLOPT_USERAGENT => 'onPHP::'.__CLASS__
			);
			
			if ($this->noBody !== null)
				$options[CURLOPT_NOBODY] = $this->noBody;
			
			if ($this->followLocation !== null)
				$options[CURLOPT_FOLLOWLOCATION] = $this->followLocation;
			
			switch ($request->getMethod()->getId()) {
				case HttpMethod::GET:
					$options[CURLOPT_HTTPGET] = true;
					
					if ($request->getGet())
						$options[CURLOPT_URL] .=
							($request->getUrl()->getQuery() ? '&' : '?')
								.$this->argumentsToString($request->getGet());
					break;
					
				case HttpMethod::POST:
					$options[CURLOPT_POST] = true;
					$options[CURLOPT_POSTFIELDS] =
					$this->argumentsToString($request->getPost());
					break;
					
				default:
					$options[CURLOPT_CUSTOMREQUEST] = $request->getMethod()->getName();
					break;
			}
			
			$headers = array();
			foreach ($request->getHeaderList() as $headerName => $headerValue) {
				$headers[] = "{$headerName}: $headerValue";
			}
			
			if ($headers) {
				$options[CURLOPT_HTTPHEADER] = $headers;
			}
			
			if ($request->getCookie()) {
				$cookies = array();
				foreach ($request->getCookie() as $name => $value)
					$cookies[] = $name.'='.urlencode($value);
				
				$options[CURLOPT_COOKIE] = implode('; ', $cookies);
			}
			
			foreach ($this->options as $key => $value) {
				$options[$key] = $value;
			}
			
			curl_setopt_array($handle, $options);
			
			return $handle;
		}
		
		/**
		 * @return CurlHttpClient
		**/
		protected function makeResponse($handle, CurlHttpResponse $response)
		{
			Assert::isNotNull($handle);
			
			$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
			try {
				$response->setStatus(
					new HttpStatus($httpCode)
				);
			} catch (MissingElementException $e) {
				throw new NetworkException(
					'curl error, strange http code: '.$httpCode
				);
			}
			
			return $this;
		}
		
		private function argumentsToString($array)
		{
			if( is_array($array) ) {
				$build = function ($array, $prefix = null) use (&$build) {
					$pairs = array();
					foreach ($array as $key => $value) {
						$key = urlencode($key);
						if ($prefix) {
							$key = $prefix . '[' . $key . ']';
						}

						if (is_object($value)) {
							throw new WrongArgumentException($key . ' is an object (' . get_class($value) . ')');
						}

						if (is_array($value)) {
							foreach ($build($value, $key) as $pair) {
								$pairs []= $pair;
							}
						} else {
							if (is_string($value)) 	$value = urlencode($value);
							if (is_bool($value))	$value = $value ? '1' : '0';

							$pairs []= $key . '=' . $value;
						}
					}
					return $pairs;
				};

				return implode('&', $build($array));
			} else {
				return $array;
			}
		}
	}
?>