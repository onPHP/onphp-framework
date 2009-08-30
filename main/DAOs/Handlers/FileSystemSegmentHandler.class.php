<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup DAOs
	**/
	final class FileSystemSegmentHandler implements SegmentHandler
	{
		private $path = null;
		
		public function __construct($segmentId)
		{
			try {
				mkdir($segmentId, 0700, true);
			} catch (BaseException $e) {
				// already created in race
			}
			
			$this->path = $segmentId;
		}
		
		public function touch($key)
		{
			try {
				return touch($this->path.$key);
			} catch (BaseException $e) {
				return false;
			}
			
			/* NOTREACHED */
		}
		
		public function unlink($key)
		{
			try {
				return unlink($this->path.$key);
			} catch (BaseException $e) {
				return false;
			}
			
			/* NOTREACHED */
		}
		
		public function ping($key)
		{
			return is_readable($this->path.$key);
		}
		
		public function drop()
		{
			// removed, but not created yet
			if (!is_writable($this->path))
				return true;
			
			$toRemove =
				realpath($this->path)
				.'.'.microtime(true)
				.'.removing';
			
			try {
				rename($this->path, $toRemove);
			} catch (BaseException $e) {
				// already removed during race
				return true;
			}

			foreach (glob($toRemove.'/*', GLOB_NOSORT) as $file)
				unlink($file);
			
			rmdir($toRemove);
			
			return true;
		}
	}
?>