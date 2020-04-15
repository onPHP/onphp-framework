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

namespace OnPHP\Main\DAO\Uncacher;

/**
 * @ingroup Uncacher
**/
class UncachersPool implements UncacherBase
{
	private $uncachers = array();

	/**
	 * @param UncacherBase $uncacher
	 * @return UncachersPool
	 */
	public static function create(UncacherBase $uncacher = null) {
		return new self($uncacher);
	}

	public function __construct(UncacherBase $uncacher = null) {
		if ($uncacher)
			$this->merge($uncacher);
	}

	public function getUncachers() {
		return $this->uncachers;
	}

	/**
	 * @param $uncacher UncacherBase same as self class
	 * @return UncacherBase (this)
	 */
	public function merge(UncacherBase $uncacher) {
		if ($uncacher instanceof UncachersPool) {
			return $this->mergeSelf($uncacher);
		}
		return $this->mergeInstance($uncacher);
	}

	public function uncache() {
		foreach ($this->uncachers as $uncacher) {
			/* @var $uncacher UncacherBase */
			$uncacher->uncache();
		}
	}

	private function mergeInstance(UncacherBase $uncacher) {
		$class = get_class($uncacher);
		if (isset($this->uncachers[$class])) {
			$this->uncachers[$class]->merge($uncacher);
		} else {
			$this->uncachers[$class] = $uncacher;
		}
		return $this;
	}

	private function mergeSelf(UncachersPool $uncacher) {
		foreach ($uncacher->getUncachers() as $subUncacher) {
			$this->merge($subUncacher);
		}
		return $this;
	}
}
?>
