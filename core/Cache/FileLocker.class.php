<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * File based locker.
	 * 
	 * @ingroup Lockers
	**/
	final class FileLocker extends BaseLocker
	{
		private $directory = null;
		
		public function __construct($directory = 'file-locking/')
		{
			$this->directory = ONPHP_TEMP_PATH.$directory;
			
			if (!is_writable($this->directory)) {
				if (!mkdir($this->directory, 0700, true)) {
					throw new WrongArgumentException(
						"can not write to '{$directory}'"
					);
				}
			}
		}
		
		public function get($key)
		{
			$this->pool[$key] = fopen($this->directory.$key, 'w+');
			
			$mseconds = 0;
			
			while (!flock($this->pool[$key], LOCK_EX)) {
				usleep(200);
				
				// give up on ten seconds timeout
				if (($mseconds += 200) > 10000) {
					return false;
				}
			}
			
			return true;
		}
		
		public function free($key)
		{
			return flock($this->pool[$key], LOCK_UN);
		}
		
		public function drop($key)
		{
			try {
				return fclose($this->pool[$key]);
			} catch (BaseException $e) {
				unset($this->pool[$key]); // already race-removed
				return false;
			}
		}
	}
?>