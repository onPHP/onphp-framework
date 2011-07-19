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
	final class PinbedPeclMemcached extends PeclMemcached
	{
		/**
		 * @return PinbedPeclMemcached 
		**/
		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT
		)
		{
			return new self($host, $port);
		}
		
		public function __construct(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT
		)
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'pecl_memcached_'.$host.'_'.$port.'_connect',
					array('pecl_memcached_connect' => $host.'_'.$port)
				);
			
			parent::__construct($host, $port);
			
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStop(
					'pecl_memcached_'.$host.'_'.$port.'_connect'
				);
		}
	}
?>