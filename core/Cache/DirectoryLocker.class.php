<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Directories based locker.
	 * 
	 * @ingroup Lockers
	**/
	namespace Onphp;

	final class DirectoryLocker extends BaseLocker
	{
		private $directory = null;
		
		protected function __construct($directory = 'dir-locking/')
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
					return $this->pool[$key] = true;
				} catch (BaseException $e) {
					// still exist
					unset($e);
					$mseconds += 200;
					usleep(200);
				}
			}
			
			return false;
		}
		
		public function free($key)
		{
			try {
				return rmdir($this->directory.$key);
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