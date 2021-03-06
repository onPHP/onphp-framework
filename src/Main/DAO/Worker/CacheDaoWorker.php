<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\DAO\Worker;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Cache\Cache;
use OnPHP\Core\OSQL\SelectQuery;
use OnPHP\Main\DAO\Uncacher\UncacherCacheDaoWorkerLists;

/**
 * Transparent and scalable DAO worker, Jedi's best choice.
 * 
 * @see CommonDaoWorker for manual-caching one.
 * @see SmartDaoWorker for locking-based worker.
 * @see VoodooDaoWorker for greedy and unscalable one.
 * 
 * @ingroup DAO
**/
class CacheDaoWorker extends TransparentDaoWorker
{
	const MAX_RANDOM_ID = 134217728;

	/// cachers
	//@{
	protected function cacheByQuery(
		SelectQuery $query,
		/* Identifiable */ $object,
		$expires = Cache::EXPIRES_FOREVER
	)
	{
		Cache::me()->mark($this->className)->
			add(
				$this->makeQueryKey($query, self::SUFFIX_QUERY),
				$object,
				$expires
			);

		return $object;
	}

	protected function cacheListByQuery(
		SelectQuery $query,
		/* array || Cache::NOT_FOUND */ $array
	)
	{
		if ($array !== Cache::NOT_FOUND) {
			Assert::isArray($array);
			Assert::isTrue(current($array) instanceof Identifiable);
		}

		Cache::me()->mark($this->className)->
			add(
				$this->makeQueryKey($query, self::SUFFIX_LIST),
				$array,
				Cache::EXPIRES_FOREVER
			);

		return $array;
	}
	//@}

	/// uncachers
	//@{
	public function uncacheLists()
	{
		return $this->registerUncacher(
			UncacherCacheDaoWorkerLists::create($this->className)
		);
	}
	//@}

	/// internal helpers
	//@{
	protected function gentlyGetByKey($key)
	{
		return Cache::me()->mark($this->className)->get($key);
	}

	protected function getLayerId()
	{
		if (
			!$result =
				Cache::me()->mark($this->className)->get($this->className)
		) {
			$result = mt_rand(1, self::MAX_RANDOM_ID);

			Cache::me()->
			mark($this->className)->
			set(
				$this->className,
				$result,
				Cache::EXPIRES_FOREVER
			);
		}

		return '@'.$result;
	}

	protected function makeQueryKey(SelectQuery $query, $suffix)
	{
		return parent::makeQueryKey($query, $suffix).$this->getLayerId();
	}
	//@}
}
?>
