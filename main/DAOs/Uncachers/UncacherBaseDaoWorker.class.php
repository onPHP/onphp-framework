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
	class UncacherBaseDaoWorker implements UncacherBase
	{
		private $classNameMap = array();
		
		/**
		 * @return UncacherBaseDaoWorker
		 */
		public static function create($className, $idKey)
		{
			return new self($className, $idKey);
		}
		
		public function __construct($className, $idKey)
		{
			$this->classNameMap[$className] = array($idKey);
		}
		
		public function getClassNameMap()
		{
			return $this->classNameMap;
		}
		
		/**
		 * @param $uncacher UncacherNullDaoWorker same as self class
		 * @return BaseUncacher (this)
		 */
		public function merge(UncacherBase $uncacher)
		{
			Assert::isInstance($uncacher, get_class($this));
			return $this->mergeSelf($uncacher);
		}
		
		public function uncache()
		{
			foreach ($this->classNameMap as $className => $idKeys) {
				foreach ($idKeys as $key) {
					$this->uncacheClassName($className, $idKeys);
				}
			}
		}
		
		protected function uncacheClassName($className, $idKeys) {
			foreach ($idKeys as $key)
				Cache::me()->mark($className)->delete($key);
		}
		
		/**
		 * @param UncacherBaseDaoWorker $uncacher
		 * @return UncacherBaseDaoWorker
		 */
		private function mergeSelf(UncacherBaseDaoWorker $uncacher)
		{
			foreach ($uncacher->getClassNameMap() as $className => $idKeys) {
				if (isset($this->classNameMap[$className])) {
					$this->classNameMap[$className] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className],
						$idKeys
					);
				} else {
					$this->classNameMap[$className] = $idKeys;
				}
			}
			return $this;
		}
	}
?>