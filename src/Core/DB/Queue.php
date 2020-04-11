<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\DB;

use OnPHP\Core\OSQL\Query;
use OnPHP\Core\Exception\UnsupportedMethodException;
use OnPHP\Core\Exception\MissingElementException;

/**
 * OSQL's queries queue.
 * 
 * @see OSQL
 * 
 * @ingroup DB
 * 
 * @todo introduce DBs without multi-query support handling
**/
final class Queue implements Query
{
	private $queue = array();

	/**
	 * @return Queue
	**/
	public static function create()
	{
		return new self;
	}

	public function getId()
	{
		return sha1(serialize($this->queue));
	}

	public function setId($id)
	{
		throw new UnsupportedMethodException();
	}

	public function getQueue()
	{
		return $this->queue;
	}

	/**
	 * @return Queue
	**/
	public function add(Query $query)
	{
		$this->queue[] = $query;

		return $this;
	}

	/**
	 * @return Queue
	**/
	public function remove(Query $query)
	{
		if (!$id = array_search($query, $this->queue))
			throw new MissingElementException();

		unset($this->queue[$id]);

		return $this;
	}

	/**
	 * @return Queue
	**/
	public function drop()
	{
		$this->queue = array();

		return $this;
	}

	/**
	 * @return Queue
	**/
	public function run(DB $db)
	{
		$db->queryRaw($this->toDialectString($db->getDialect()));

		return $this;
	}

	/**
	 * @return Queue
	**/
	public function flush(DB $db)
	{
		return $this->run($db)->drop();
	}

	// to satisfy Query interface
	public function toString()
	{
		return $this->toDialectString(ImaginaryDialect::me());
	}

	public function toDialectString(Dialect $dialect)
	{
		$out = array();

		foreach ($this->queue as $query)
			$out[] = $query->toDialectString($dialect);

		return implode(";\n", $out);
	}
}
?>
