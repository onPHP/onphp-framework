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

	/**
	 * @ingroup Http
	**/
	namespace Onphp;

	interface HttpClient
	{
		/**
		 * @param $timeout int in seconds
		**/
		public function setTimeout($timeout);
		public function getTimeout();
		
		/**
		 * whether to follow header Location or not
		**/
		public function setFollowLocation(/* boolean */ $really);
		public function isFollowLocation();
		
		/**
		 * maximum number of header Location followed
		**/
		public function setMaxRedirects($maxRedirects);
		public function getMaxRedirects();
		
		/**
		 * @param $request HttpRequest
		 * @return \Onphp\HttpResponse
		**/
		public function send(HttpRequest $request);

		/**
		 * @param $key string
		 * @param $value string
		 * @return HttpClient
		**/
		public function setOption($key, $value);

		/**
		 * @param $key string
		 * @return HttpClient
		**/
		public function dropOption($key);

		public function getOption($key);

		/**
		 * @param $really boolean
		 * @return HttpClient
		**/
		public function setNoBody($really);

		public function hasNoBody();

		/**
		 * @param $maxFileSize int
		 * @return HttpClient
		**/
		public function setMaxFileSize($maxFileSize);

		public function getMaxFileSize();
	}
?>