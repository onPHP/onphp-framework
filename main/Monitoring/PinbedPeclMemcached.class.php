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
		/**
		 * @return PinbedPeclMemcached 
		**/
		public static function create(
			$host = Memcached::DEFAULT_HOST,
			$port = Memcached::DEFAULT_PORT,
			$connectTimeout = PeclMemcached::DEFAULT_TIMEOUT
		)
		{
			return new self($host, $port);
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
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$this->log(__METHOD__.$action);
			
			$result = parent::store($action, $key, $value, $expires);
			
			$this->stopLog(__METHOD__.$action);
			
			return $result;
			
		}
		
		protected function connect()
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'pecl_memcached_'.$this->host.'_'.$this->port.'_connect',
					array('pecl_memcached_connect' => $this->host.'_'.$this->port)
				);
			
			parent::connect();
			
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStop(
					'pecl_memcached_'.$this->host.'_'.$this->port.'_connect'
				);
		}
		
		/*void */ private function log($methodName)
		{
			if (PinbaClient::isEnabled())
				PinbaClient::me()->timerStart(
					'pecl_memcached_'.$this->host.'_'.$this->port.'_'.$methodName,
					array(
						'group'				=> 'cache',
						'memcached_server'	=> $this->host.'::'.$this->port,
						'memcached_type'	=> $methodName
					)
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