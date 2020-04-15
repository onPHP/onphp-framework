<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
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
use OnPHP\Core\Cache\WatermarkedPeer;
use OnPHP\Core\OSQL\SelectQuery;
use OnPHP\Main\DAO\GenericDAO;
use OnPHP\Main\DAO\Handlers\ApcSegmentHandler;
use OnPHP\Main\DAO\Handlers\CacheSegmentHandler;
use OnPHP\Main\DAO\Handlers\eAcceleratorSegmentHandler;
use OnPHP\Main\DAO\Handlers\MessageSegmentHandler;
use OnPHP\Main\DAO\Handlers\SegmentHandler;
use OnPHP\Main\DAO\Handlers\SharedMemorySegmentHandler;
use OnPHP\Main\DAO\Handlers\XCacheSegmentHandler;
use OnPHP\Main\DAO\Uncacher\UncacherVoodoDaoWorkerLists;

/**
 * Transparent though quite obscure and greedy DAO worker.
 * 
 * @warning Do not ever think about using it on production systems, unless
 * you're fully understand every line of code here.
 * 
 * @magic you'll probably want to tweak your
 * sysctl when using MessageSegmentHandler:
 * 
 * kernel.msgmni = (total number of DAOs + 2)
 * kernel.msgmnb = 32767
 * 
 * @see CommonDaoWorker for manual-caching one.
 * @see SmartDaoWorker for less obscure, but locking-based worker.
 * 
 * @ingroup DAO
**/
final class VoodooDaoWorker extends TransparentDaoWorker
{
	private $classKey = null;
	/**
	 * @var SegmentHandler
	 */
	private $handler = null;

	// will trigger auto-detect
	private static $defaultHandler = null;

	public static function setDefaultHandler($handler)
	{
		Assert::classExists($handler);

		self::$defaultHandler = $handler;
	}

	public function __construct(GenericDAO $dao)
	{
		parent::__construct($dao);

		if (($cache = Cache::me()) instanceof WatermarkedPeer)
			$watermark = $cache->mark($this->className)->getActualWatermark();
		else
			$watermark = null;

		$this->classKey = $this->keyToInt($watermark.$this->className);

		$this->handler = $this->spawnHandler($this->classKey);
	}

	/// cachers
	//@{
	protected function cacheByQuery(
		SelectQuery $query,
		/* Identifiable */ $object,
		$expires = Cache::EXPIRES_FOREVER
	)
	{
		$key = $this->makeQueryKey($query, self::SUFFIX_QUERY);

		if ($this->handler->touch($this->keyToInt($key)))
			Cache::me()->mark($this->className)->
				add($key, $object, $expires);

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

		$cache = Cache::me();

		$key = $this->makeQueryKey($query, self::SUFFIX_LIST);

		if ($this->handler->touch($this->keyToInt($key))) {
			$cache->mark($this->className)->
				add($key, $array, Cache::EXPIRES_FOREVER);
		}

		return $array;
	}
	//@}

	/// uncachers
	//@{
	public function uncacheLists()
	{
		return $this->registerUncacher(
			UncacherVoodoDaoWorkerLists::create($this->className, $this->handler)
		);
	}
	//@}

	/// internal helpers
	//@{
	protected function gentlyGetByKey($key)
	{
		if ($this->handler->ping($this->keyToInt($key)))
			return Cache::me()->mark($this->className)->get($key);
		else {
			Cache::me()->mark($this->className)->delete($key);
			return null;
		}
	}

	private function spawnHandler($classKey)
	{
		if (!self::$defaultHandler) {
			if (extension_loaded('sysvshm')) {
				$handlerName = SharedMemorySegmentHandler::class;
			} elseif (extension_loaded('sysvmsg')) {
				$handlerName = MessageSegmentHandler::class;
			} else {
				if (extension_loaded('eaccelerator')) {
					$handlerName = eAcceleratorSegmentHandler::class;
				} elseif (extension_loaded('apc')) {
					$handlerName = ApcSegmentHandler::class;
				} elseif (extension_loaded('xcache')) {
					$handlerName = XCacheSegmentHandler::class;
				} else {
					$handlerName = CacheSegmentHandler::class;
				}
			}
		} else {
			$handlerName = self::$defaultHandler;
		}

		if (!self::$defaultHandler)
			self::$defaultHandler = $handlerName;

		return new self::$defaultHandler($classKey);
	}
	//@}
}
?>