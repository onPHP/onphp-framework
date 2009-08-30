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
	 * File-system based adoption of VoodooDaoWroker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for less obscure, but locking-based worker.
	 * @see VoodooDaoWorker for greedy Sys-V shared memory based parent.
	 * 
	 * @ingroup DAOs
	**/
	final class FileSystemDaoWorker extends VoodooDaoWorker
	{
		private $segmentPath = null;
		
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);
			
			if (($cache = Cache::me()) instanceof WatermarkedPeer)
				$watermark = $cache->getWatermark().DIRECTORY_SEPARATOR;
			else
				$watermark = null;
			
			$this->segmentPath =
				ONPHP_TEMP_PATH
				.'fsdw'.DIRECTORY_SEPARATOR
				.$watermark
				.$this->classKey
				.DIRECTORY_SEPARATOR;
			
			if (!is_writable($this->segmentPath))
				mkdir($this->segmentPath, 0700, true);
		}
		
		//@{
		// uncachers
		public function uncacheLists()
		{
			// removed, but not created yet
			if (!is_writable($this->segmentPath))
				return true;
			
			$toRemove =
				realpath($this->segmentPath)
				.'.'.microtime(true)
				.'.removing';
			
			try {
				rename($this->segmentPath, $toRemove);
			} catch (BaseException $e) {
				// already removed during race
				return true;
			}

			foreach (glob($toRemove.'/*', GLOB_NOSORT) as $file)
				unlink($file);
			
			rmdir($toRemove);
			
			return true;
		}
		//@}
		
		//@{
		// internal helpers
		protected function touch($key)
		{
			try {
				return touch($this->segmentPath.$this->keyToInt($key, 15));
			} catch (BaseException $e) {
				return false;
			}
			
			/* NOTREACHED */
		}
		
		protected function unlink($key)
		{
			try {
				return unlink($this->segmentPath.$this->keyToInt($key, 15));
			} catch (BaseException $e) {
				return false;
			}
			
			/* NOTREACHED */
		}
		
		protected function ping($key)
		{
			return is_readable($this->segmentPath.$this->keyToInt($key, 15));
		}
		//@}
	}
?>