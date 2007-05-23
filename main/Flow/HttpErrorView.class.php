<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class HttpErrorView implements View
	{
		protected $prefix	= null;
		protected $code		= null;
		protected $postfix	= null;
		
		public function __construct($prefix, $code, $postfix)
		{
			$this->prefix = $prefix;
			$this->code = $code;
			$this->postfix = $postfix;
		}
		
		public function render($model = null)
		{
			$messages = array(
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Large',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version not supported'
			);
			
			if (isset($messages[$this->code]))
				$message = $messages[$this->code];
			else
				$message = null;

			header('HTTP/1.0 '.$this->code.' '.$message);
			include $this->prefix.$this->code.$this->postfix;
		}
	}
?>