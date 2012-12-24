<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Sys-V shared memory cache.
	 * 
	 * @ingroup Cache
	**/
	final class SharedMemory extends SelectivePeer
	{
		const INDEX_SEGMENT			= 12345678;
		
		const DEFAULT_SEGMENT_SIZE	= 4194304; // 128^3 * 2
		
		private $defaultSize = null;
		private $customSized = array();
		
		private static $attached = array();
		
		/**
		 * @return SharedMemory
		**/
		public static function create(
			$defaultSize = self::DEFAULT_SEGMENT_SIZE,
			$customSized = array()
		)
		{
			return new self($defaultSize, $customSized);
		}
		
		/**
		 * @return SharedMemory
		**/
		public function __construct(
			$defaultSize = self::DEFAULT_SEGMENT_SIZE,
			$customSized = array() // 'className' => segmentSizeInBytes
		)
		{
			$this->defaultSize = $defaultSize;
			$this->customSized = $customSized;
		}
		
		public function __destruct()
		{
			foreach (self::$attached as $segment)
				shm_detach($segment);
			
			// sync classes
			$segment = shm_attach(
				self::INDEX_SEGMENT, self::DEFAULT_SEGMENT_SIZE, ONPHP_IPC_PERMS
			);
			
			try {
				$index = shm_get_var($segment, 1);
			} catch (BaseException $e) {
				$index = array();
			}
			
			try {
				shm_put_var(
					$segment,
					1,
					array_unique(
						array_merge(
							$index, array_keys(self::$attached)
						)
					)
				);
			} catch (BaseException $e) {/*_*/}
			
			try {
				shm_detach($segment);
			} catch (BaseException $e) {/*_*/}
		}
		
		public function increment($key, $value)
		{
			if (null !== ($current = $this->get($key))) {
				$this->set($key, $current += $value);
				return $current;
			}
			
			return null;
		}
		
		public function decrement($key, $value)
		{
			if (null !== ($current = $this->get($key))) {
				$this->set($key, $current -= $value);
				return $current;
			}
			
			return null;
		}
		
		public function get($key)
		{
			$segment = $this->getSegment();
			
			$key = $this->stringToInt($key);
			
			try {
				$stored = shm_get_var($segment, $key);
				
				if ($stored['expires'] <= time()) {
					$this->delete($key);
					return null;
				}
				
				return $this->restoreData($stored['value']);
				
			} catch (BaseException $e) {
				// not found there
				return null;
			}
			
			Assert::isUnreachable();
		}
		
		public function delete($key)
		{
			try {
				return shm_remove_var(
					$this->getSegment(), $this->stringToInt($key)
				);
			} catch (BaseException $e) {
				return false;
			}
			
			Assert::isUnreachable();
		}
		
		public function isAlive()
		{
			// any better idea how to detect shm-availability?
			return true;
		}
		
		/**
		 * @return SharedMemory
		**/
		public function clean()
		{
			$segment = shm_attach(self::INDEX_SEGMENT);
			
			try {
				$index = shm_get_var($segment, 1);
			} catch (BaseException $e) {
				// nothing to clean
				return null;
			}
			
			foreach ($index as $key) {
				try {
					$sem = shm_attach($this->stringToInt($key));
					shm_remove($sem);
				} catch (BaseException $e) {
					// already removed, probably
				}
			}
			
			shm_remove($segment);
			
			return parent::clean();
		}
		
		public function append($key, $data)
		{
			$segment = $this->getSegment();
			
			$key = $this->stringToInt($key);
			
			try {
				$stored = shm_get_var($segment, $key);
				
				if ($stored['expires'] <= time()) {
					$this->delete($key);
					return false;
				}
				
				return $this->store(
					'ignored',
					$key,
					$stored['value'].$data,
					$stored['expires']
				);
			} catch (BaseException $e) {
				// not found there
				return false;
			}
		}
		
		protected function store($action, $key, $value, $expires = 0)
		{
			$segment = $this->getSegment();
			
			if ($expires < parent::TIME_SWITCH)
				$expires += time();
			
			try {
				shm_put_var(
					$segment,
					$this->stringToInt($key),
					array(
						'value' => $this->prepareData($value),
						'expires' => $expires
					)
				);
				
				return true;
				
			} catch (BaseException $e) {
				// not enough memory
				return false;
			}
			
			Assert::isUnreachable();
		}
		
		private function getSegment()
		{
			$class = $this->getClassName();
			
			if (!isset(self::$attached[$class]))
				self::$attached[$class] = shm_attach(
					$this->stringToInt($class),
					isset($this->customSized[$class])
						? $this->customSized[$class]
						: $this->defaultSize,
					ONPHP_IPC_PERMS
				);
			
			return self::$attached[$class];
		}
		
		private function stringToInt($string)
		{
			return hexdec(substr(md5($string), 0, 8));
		}
	}
