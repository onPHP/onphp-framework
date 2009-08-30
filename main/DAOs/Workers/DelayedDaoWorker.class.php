<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Message-based version of VoodooDaoWroker.
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for less obscure, but locking-based worker.
	 * @see VoodooDaoWorker for greedy Sys-V shared memory based parent.
	 * @see FileSystemDaoWorker for Voodoo's filesystem-based child.
	 * 
	 * @ingroup DAOs
	**/
	final class DelayedDaoWorker extends VoodooDaoWorker
	{
		protected $precision = 6;
		
		public function __construct(GenericDAO $dao)
		{
			parent::__construct($dao);
			
			$this->handler = new MessageSegmentHandler($this->classKey);
		}
	}
?>