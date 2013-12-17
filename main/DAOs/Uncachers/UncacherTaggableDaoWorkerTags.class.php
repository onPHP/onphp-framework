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

	class UncacherTaggableDaoWorkerTags implements UncacherBase
	{
		private $classNameMap = array();
		
		/**
		 * @return UncacherTaggableDaoWorkerTags
		 */
		public static function create($className, array $tags = array())
		{
			return new self($className, $tags);
		}
		
		public function __construct($className, array $tags = array())
		{
			$this->classNameMap[$className] = $tags;
		}
		
		/**
		 * @return array
		 */
		public function getClassNameMap()
		{
			return $this->classNameMap;
		}
		/**
		 * @param $uncacher UncacherTaggableDaoWorkerTags same as self class
		 * @return UncacherBase (this)
		 */
		public function merge(UncacherBase $uncacher)
		{
			Assert::isInstance($uncacher, '\Onphp\UncacherTaggableDaoWorkerTags');
			return $this->mergeSelf($uncacher);
		}
		
		public function uncache()
		{
			foreach ($this->classNameMap as $className => $tags) {
				$dao = ClassUtils::callStaticMethod("$className::dao");
				/* @var $dao StorableDAO */
				$worker = Cache::worker($dao);
				Assert::isInstance($worker, '\Onphp\TaggableDaoWorker');
				/* @var $worker TaggableDaoWorker */
				
				$worker->expireTags($tags);
			}
		}
		
		private function mergeSelf(UncacherTaggableDaoWorkerTags $uncacher) {
			foreach ($uncacher->getClassNameMap() as $className => $tags) {
				if (!isset($this->classNameMap[$className])) {
					$this->classNameMap[$className] = $tags;
				} else {
					//merging idkeys
					$this->classNameMap[$className] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className],
						$tags
					);
				}
			}
			return $this;
		}
	}
?>