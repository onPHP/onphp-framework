<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Enumeration of http status codes
	 * 
	 * @ingroup Http
	**/	
	final class HttpStatus extends Enumeration
	{
		const CODE_100	= 100;
		const CODE_101	= 101;
		const CODE_200	= 200;
		const CODE_201	= 201;
		const CODE_202	= 202;
		const CODE_203	= 203;
		const CODE_204	= 204;
		const CODE_205	= 205;
		const CODE_206	= 206;
		const CODE_300	= 300;
		const CODE_301	= 301;
		const CODE_302	= 302;
		const CODE_303	= 303;
		const CODE_304	= 304;
		const CODE_305	= 305;
		const CODE_307	= 307;
		const CODE_400	= 400;
		const CODE_401	= 401;
		const CODE_402	= 402;
		const CODE_403	= 403;
		const CODE_404	= 404;
		const CODE_405	= 405;
		const CODE_406	= 406;
		const CODE_407	= 407;
		const CODE_408	= 408;
		const CODE_409	= 409;
		const CODE_410	= 410;
		const CODE_411	= 411;
		const CODE_412	= 412;
		const CODE_413	= 413;
		const CODE_414	= 414;
		const CODE_415	= 415;
		const CODE_416	= 416;
		const CODE_417	= 417;
		const CODE_500	= 500;
		const CODE_501	= 501;
		const CODE_502	= 502;
		const CODE_503	= 503;
		const CODE_504	= 504;
		const CODE_507	= 507;
		const CODE_510	= 510;
		
		protected $names = array(
			self::CODE_100 => 'Continue',
			self::CODE_101 => 'Switching Protocols',
			self::CODE_200 => 'OK',
			self::CODE_201 => 'Created',
			self::CODE_202 => 'Accepted',
			self::CODE_203 => 'Non-Authoritative Information',
			self::CODE_204 => 'No Content',
			self::CODE_205 => 'Reset Content',
			self::CODE_206 => 'Partial Content',
			self::CODE_300 => 'Multiple Choices',
			self::CODE_301 => 'Moved Permanently',
			self::CODE_302 => 'Found',
			self::CODE_303 => 'See Other',
			self::CODE_304 => 'Not Modified',
			self::CODE_305 => 'Use Proxy',
			self::CODE_307 => 'Temporary Redirect',
			self::CODE_400 => 'Bad Request',
			self::CODE_401 => 'Unauthorized',
			self::CODE_402 => 'Payment Required',
			self::CODE_403 => 'Forbidden',
			self::CODE_404 => 'Not Found',
			self::CODE_405 => 'Method Not Allowed',
			self::CODE_406 => 'Not Acceptable',
			self::CODE_407 => 'Proxy Authentication Required',
			self::CODE_408 => 'Request Time-out',
			self::CODE_409 => 'Conflict',
			self::CODE_410 => 'Gone',
			self::CODE_411 => 'Length Required',
			self::CODE_412 => 'Precondition Failed',
			self::CODE_413 => 'Request Entity Too Large',
			self::CODE_414 => 'Request-URI Too Large',
			self::CODE_415 => 'Unsupported Media Type',
			self::CODE_416 => 'Requested range not satisfiable',
			self::CODE_417 => 'Expectation Failed',
			self::CODE_500 => 'Internal Server Error',
			self::CODE_501 => 'Not Implemented',
			self::CODE_502 => 'Bad Gateway',
			self::CODE_503 => 'Service Unavailable',
			self::CODE_504 => 'Gateway Time-out',
			self::CODE_507 => 'Insufficient Storage',
			self::CODE_510 => 'Not Extended'
		);
		
		public static function getAnyId()
		{
			return 200;
		}
		
		public function toString()
		{
			return 'HTTP/1.1 '.$this->id.' '.$this->name;
		}

		public function isRedirection()
		{
			return $this->getId() >= 300 && $this->getId() < 400;
		}
	}
?>