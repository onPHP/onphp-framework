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

	class UncacherTaggableDaoWorker implements UncacherBase
	{
		private $classNameMap = array();
		
		/**
		 * @return \Onphp\UncacherTaggableDaoWorker
		 */
		public static function create($className, $idKey, $tags, TaggableDaoWorker $worker)
		{
			return new self($className, $idKey, $tags, $worker);
		}
		
		public function __construct($className, $idKey, $tags, TaggableDaoWorker $worker)
		{
			$this->classNameMap[$className] = array(array($idKey), $tags, $worker);
		}
		
		/**
		 * @return array
		 */
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
			Assert::isInstance($uncacher, '\Onphp\UncacherTaggableDaoWorker');
			return $this->mergeSelf($uncacher);
		}
		
		public function uncache()
		{
			foreach ($this->classNameMap as $className => $uncaches) {
				list($idKeys, $tags, $worker) = $uncaches;
				/* @var $worker \Onphp\TaggableDaoWorker */
				$worker->expireTags($tags);
				
				foreach ($idKeys as $key)
					Cache::me()->mark($className)->delete($idKey);
				
				ClassUtils::callStaticMethod("$className::dao")->uncacheLists();
			}
		}
		
		private function mergeSelf(UncacherTaggableDaoWorker $uncacher) {
			foreach ($uncacher->getClassNameMap() as $className => $uncaches) {
				if (!isset($this->classNameMap[$className])) {
					$this->classNameMap[$className] = $uncaches;
				} else {
					//merging idkeys
					$this->classNameMap[$className][0] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className][0],
						$uncaches[0]
					);
					//merging tags
					$this->classNameMap[$className][1] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className][1],
						$uncaches[1]
					);
				}
			}
			return $this;
		}
	}
?>