<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Uncachers
	**/
	namespace Onphp;

	class UncacherCacheDaoWorkerLists implements UncacherBase
	{
		private $classNameList = array();
		
		/**
		 * @return \Onphp\UncacherBaseDaoWorker
		 */
		public static function create($className)
		{
			return new self($className);
		}
		
		public function __construct($className)
		{
			$this->classNameList[$className] = $className;
		}
		
		public function getClassNameList()
		{
			return $this->classNameList;
		}
		
		/**
		 * @param $uncacher UncacherCacheDaoWorkerLists same as self class
		 * @return BaseUncacher (this)
		 */
		public function merge(UncacherBase $uncacher)
		{
			Assert::isInstance($uncacher, get_class($this));
			return $this->mergeSelf($uncacher);
		}
		
		public function uncache()
		{
			foreach ($this->classNameList as $className) {
				$this->uncacheClassName($className);
			}
		}
		
		private function uncacheClassName($className)
		{
			if (
				!Cache::me()->
					mark($className)->
					increment($className, 1)
			)
				Cache::me()->mark($className)->delete($className);
		}
		
		/**
		 * @param \Onphp\UncacherCacheDaoWorkerLists $uncacher
		 * @return \Onphp\UncacherCacheDaoWorkerLists
		 */
		private function mergeSelf(UncacherCacheDaoWorkerLists $uncacher)
		{
			foreach ($uncacher->getClassNameList() as $className) {
				if (!isset($this->classNameList[$className]))
					$this->classNameList[$className] = $className;
			}
			return $this;
		}
	}
?>