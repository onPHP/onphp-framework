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
		private $directory	= null;
		
		public static function create($directory = '/tmp/onPHP/cache/')
		{
			return new RubberFileSystem($directory);
		}

		public function __construct($directory = '/tmp/onPHP/cache/')
		{
			if (!is_writable($directory))
				throw new WrongArgumentException();
			
			if ($directory{strlen($directory) - 1} != DIRECTORY_SEPARATOR)
				$directory .= DIRECTORY_SEPARATOR;
			
			$this->directory = $directory;
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
					@unlink($path);
					return null;
				}
				
				return $this->operate($path);
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
			
			$this->operate($path, $value);
			
			if ($expires < parent::TIME_SWITCH)
				$expires += time();

			touch($path, time() + $expires);
			
			return true;
		}
		
		private function operate($path, $value = null)
		{
			$key = hexdec(substr(md5($path), 1, 8));
			
			$sem = sem_get($key, 1, 0644, true);
			
			if (!sem_acquire($sem))
				return null;
			
			try {
				$fp = fopen($path, $value !== null ? 'wb' : 'rb');
			} catch (BaseException $e) {
				sem_remove($sem);
				return null;
			}
			
			try {
				flock($fp, $value !== null ? LOCK_EX : LOCK_SH);
			} catch (BaseException $e) {
				sem_remove($sem);
				return null;
			}
			
			if ($value !== null) {
				fwrite($fp, $this->prepareData($value));
				fclose($fp);
				sem_remove($sem);
				
				return;
			} else {
				$data = fread($fp, filesize($path));
				fclose($fp);
				sem_remove($sem);
				
				return $this->restoreData($data);
			}
			
			/* NOTREACHED */
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