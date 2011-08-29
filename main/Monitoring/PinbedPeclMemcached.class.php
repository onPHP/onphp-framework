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
	 *
	**/
	final class PinbedPeclMemcached extends PeclMemcached
	{
		private $host = null;
		private $port = null;
		
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
			$this->host = $host;
			$this->port = $port;
			
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
		
		public function append($key, $data)
		{
			$this->log(__METHOD__);
			$result = parent::append($key, $data);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		public function decrement($key, $value)
		{
			$this->log(__METHOD__);
			$result = parent::decrement($key, $value);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		public function delete($index)
		{
			$this->log(__METHOD__);
			$result = parent::delete($index);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		public function get($index)
		{
			$this->log(__METHOD__);
			$result = parent::get($index);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		public function getList($indexes)
		{
			$this->log(__METHOD__);
			$result = parent::getList($indexes);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		public function increment($key, $value)
		{
			$this->log(__METHOD__);
			$result = parent::increment($key, $value);
			$this->stopLog(__METHOD__);
			
			return $result;
		}
		
		/*void */ private function log($methodName)
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'pecl_memcached_'.$this->host.'_'.$this->port.'_'.$methodName,
					array('pecl_memcached_'.__METHOD__ => $this->host.'_'.$this->port)
				);
		}
		
		/*void */ private function stopLog($methodName)
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStop(
					'pecl_memcached_'.$this->host.'_'.$this->port.'_'.$methodName
				);
		}
	}
?>