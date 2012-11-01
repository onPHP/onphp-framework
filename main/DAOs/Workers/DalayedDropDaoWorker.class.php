<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * DAO worker with dealyed object drop from cache
	 * 
	 * @see CommonDaoWorker for manual-caching one.
	 * @see SmartDaoWorker for transparent one.
	 * 
	 * @ingroup DAOs
	**/
	namespace Onphp;

	final class DalayedDropDaoWorker extends NullDaoWorker
	{
		private $modifiedIds = array();
		
		/// uncachers
		//@{
		public function uncacheById($id)
		{
			$this->modifiedIds[$id] = $id;
			
			return true;
		}
		
		/**
		 * @param mixed $id
		 * @return \Onphp\UncacherBase
		 */
		public function getUncacherById($id) {
			return UncacherNullDaoWorker::create();
		}
		
		public function dropWith($worker)
		{
			Assert::classExists($worker);
			
			if ($this->modifiedIds) {
				$workerObject = new $worker($this->dao);
				
				$workerObject->uncacheByIds($this->modifiedIds);
				
				$this->modifiedIds = array();
			}
			
			return $this;
		}
		//@}
	}
?>
