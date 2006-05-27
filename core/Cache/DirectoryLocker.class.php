<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Directories based locker.
	 * 
	 * @ingroup Lockers
	**/
	final class DirectoryLocker extends BaseLocker
	{
		private $directory = null;
		
		public function __construct($directory = 'dir-locking/')
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
			$mseconds = 0;
			
			while ($mseconds < 10000) {
				try {
					mkdir($this->directory.$key, 0700, false);
					$this->pool[$key] = true;
					break;
				} catch (BaseException $e) {
					// still exist
					$mseconds += 200;
					usleep(200);
				}
			}
			
			return isset($this->pool[$key]);
		}
		
		public function free($key)
		{
			try {
				rmdir($this->directory.$key);
			} catch (BaseException $e) {
				return false;
			}
		}
		
		public function drop($key)
		{
			return $this->free($key);
		}
	}
?>