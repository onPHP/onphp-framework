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
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);
			
			if (($cache = Cache::me()) instanceof WatermarkedPeer)
				$watermark = $cache->getWatermark().DIRECTORY_SEPARATOR;
			else
				$watermark = null;
			
			$path =
				ONPHP_TEMP_PATH
				.'fsdw'.DIRECTORY_SEPARATOR
				.$watermark
				.$this->classKey
				.DIRECTORY_SEPARATOR;

			$this->handler = new FileSystemSegmentHandler($path);
		}
	}
?>