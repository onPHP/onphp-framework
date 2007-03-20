<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * File-system based adoption of VoodooDaoWroker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for less obscure, but locking-based worker.
	 * @see VoodooDaoWorker for greedy Sys-V shared memory based parent.
	 * @see DelayedDaoWorker for message-based asynchronous one.
	 * 
	 * @ingroup DAOs
	**/
	final class FileSystemDaoWorker extends VoodooDaoWorker
	{
		protected function spawnHandler($classKey)
		{
			return new FileSystemSegmentHandler($classKey);
		}
	}
?>