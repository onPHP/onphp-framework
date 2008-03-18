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
	final class CurlHttpClient implements HttpClient
	{
		private $options		= array();
		
		private $followLocation	= null;
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
		 * @param $timeout in seconds
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
			
			foreach ($this->options as $key => $value) {
				$options[$key] = $value;
			}
			
			curl_setopt_array($handle, $options);
			
			if (curl_exec($handle) === false) {
				$code = curl_errno($handle);
				throw new NetworkException(
					'curl error, code: '.$code
						.' description: '.curl_error($handle),
					$code
				);
			}
			
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
			
			curl_close($handle);
			
			return $response;
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