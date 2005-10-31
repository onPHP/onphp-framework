<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Simple filesystem cache requiring external manual cleanup.
	**/
	final class RubberFileSystem extends CachePeer
	{
		const TIME_SWITCH = 2592000; // 60 * 60 * 24 * 30
		
		private $directory	= null;
		
		public static function create($directory = '/tmp/onPHP/cache/')
		{
			return new RubberFileSystem($directory);
		}

		public function __construct($directory = '/tmp/onPHP/cache/')
		{
			if (!is_writable($directory))
				return null;
			
			if ($directory{strlen($directory) - 1} != DIRECTORY_SEPARATOR)
				$directory .= DIRECTORY_SEPARATOR;
			
			$this->directory	= $directory;
		}
		
		public function isAlive()
		{
			return is_writable($this->directory);
		}
		
		public function clean()
		{
			parent::clean();
			
			// TODO: rm -rf $this->directory
		}

		public function get($key)
		{
			$path = $this->makePath($key);
			
			if (is_readable($path)) {
				
				if (filemtime($path) <= time()) {
					var_dump(filemtime($path), time());
					
					@unlink($path);
					return null;
				}
				
				$sem = null;

				if (!$fp = $this->getFilePointer($sem, $path, true))
					return null;

				$data = fread($fp, filesize($path));
				
				fclose($fp); sem_release($sem);
				
				return $this->restoreData($data);
			}
			
			return null;
		}
		
		public function delete($key)
		{
			try {
				unlink($this->makePath($key));
				return true;
			} catch (BaseException $e) {
				return false;
			}
		}
		
		protected function store($action, $key, &$value, $expires = 0)
		{
			$path = $this->makePath($key);
			$time = time();
			
			$directory = dirname($path);
			
			if (!file_exists($directory))
				mkdir($directory);
			
			$sem = null;
			
			// do not add, if file exist and not expired
			if (
				$action == 'add'
				&& is_readable($path)
				&& filemtime($path) > $time
			)
				return true;
			
			// do not replace, when file not exist or expired
			if (
				$action == 'replace'
			) {
				if (!is_readable($path)) {
					return false;
				} elseif (filemtime($path) <= $time) {
					$this->delete($key);
					return false;
				}
			}
			
			if (!$fp = $this->getFilePointer($sem, $path, false))
				return false;
			
			fwrite($fp, $this->prepareData($value));
			
			fclose($fp); sem_release($sem);
			
			if ($expires < self::TIME_SWITCH)
				$expires += time();

			touch($path, time() + $expires);
			
			return true;
		}
				
		private function getFilePointer(&$semaphore, $path, $readOnly = true)
		{
			$semaphore = sem_get(hexdec(substr(md5($path), 3, 7)), 1, 0600, true);
			
			if (!sem_acquire($semaphore))
				return null;
			
			try {
				$fp = fopen(
					$path,
					$readOnly === false ? 'wb' : 'rb'
				);
			} catch (BaseException $e) {
				sem_release($semaphore);
				return null;
			}
			
			try {
				flock(
					$fp,
					$readOnly === false ? LOCK_EX : LOCK_SH
				);
				
				return $fp;
			} catch (BaseException $e) {
				sem_release($semaphore);
				fclose($fp);
			}
			
			return null;
		}
		
		private function makePath($key)
		{
			return
				$this->directory
				.$key{0}.$key{1}
				.DIRECTORY_SEPARATOR
				.substr($key, 2);
		}
	}
?>