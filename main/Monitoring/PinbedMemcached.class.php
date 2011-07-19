<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
	
	/**
	 *  @ingroup Cache
	**/
	final class PinbedMemcached extends Memcached
	{
		/**
		 * @return PinbedMemcached 
		**/
		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$buffer = Memcached::DEFAULT_BUFFER
		)
		{
			return new self($host, $port, $buffer);
		}
		
		public function __construct(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$buffer = Memcached::DEFAULT_BUFFER
		)
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'memcached_'.$host.'_'.$port.'_connect',
					array('memcached_connect' => $host.'_'.$port)
				);
			
			parent::__construct($host, $port, $buffer);
			
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStop(
					'memcached_'.$host.'_'.$port.'_connect'
				);
		}
	}
?>