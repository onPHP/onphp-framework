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
	interface HttpClient
	{
		/**
		 * @param $timeout in seconds
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
		 * @return HttpResponse
		**/
		public function send(HttpRequest $request);
	}
?>