<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */


	/**
	 * Try to identify mobile device by http headers
	 * 
	 * @ingroup Utils
	**/
	
	final class MobileRequestDetektor
	{
		static private $headers = array(
			'HTTP_X_WAP_PROFILE',
			// msisdn stuff
			'HTTP_MSISDN',
			'HTTP_X_MSISDN',
			'HTTP_X_NOKIA_MSISDN',
			'HTTP_X_WAP_NETWORK_CLIENT_MSISDN',
			'HTTP_X_UP_CALLING_LINE_ID',
			'HTTP_X_NETWORK_INFO',
			// ms specific headers
			'HTTP_UA_PIXELS',
			'HTTP_UA_COLOR',
			'HTTP_UA_OS',
			'HTTP_UA_CPU',
			'HTTP_UA_VOICE',
			// misc
			'HTTP_X_NOKIA_BEARER',
			'HTTP_X_NOKIA_GATEWAY_ID',
			'HTTP_X_NOKIA_WIA_ACCEPT_ORIGINAL',
			'HTTP_X_NOKIA_CONNECTION_MODE',
			'HTTP_X_NOKIA_WTLS',
			'HTTP_X_WAP_PROXY_COOKIE',
			'HTTP_X_WAP_TOD_CODED',
			'HTTP_X_WAP_TOD',
			'HTTP_X_UNIQUEWCID',
			'HTTP_WAP_CONNECTION'
		);
		
		/**
		 * @return MobileRequestDetektor
		**/
		public static function create()
		{
			return new self;
		}
		
		public function isOperaMni(HttpRequest $request)
		{
			// mandatory opera mini header
			return $request->hasServerVar('HTT_X_OPERAMINI_FEATURES');
		}
		
		public function isMobile(HttpRequest $request)
		{
			if ($this->isOperaMni($request))
				return true;
			
			foreach (self::$headers as $header)
				if ($request->hasServerVar($header))
					return true;
			
			return false;
		}
	}
?>