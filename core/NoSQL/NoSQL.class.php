<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 27.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * NoSQL-connector's implementation basis.
 *
 * @ingroup NoSQL
**/
abstract class NoSQL extends DB {

	protected $link		= null;

	// credentials
	protected $username	= null;
	protected $password	= null;
	protected $hostname	= null;
	protected $port		= null;
	protected $basename	= null;
	protected $encoding	= null;

	// queries
	abstract public function selectOne($table, $key);
	abstract public function selectList($table, array $keys);
	abstract public function insert($table, array $row);
	abstract public function update($table, array $row);
	abstract public function deleteOne($table, $key);
	abstract public function deleteList($table, array $keys);

	// full table queries
	abstract public function getPlainList($table);
	abstract public function getTotalCount($table);

	// custom queries
	abstract public function getListByField($table, $field, $value, Criteria $criteria = null);
	abstract public function getIdListByField($table, $field, $value, Criteria $criteria = null);
	abstract public function find($table, $query);
//	abstract public function count($table, $query);

	public function getTableInfo($table) {
		throw new UnsupportedMethodException('Can not execute getTableInfo in NoSQL');
	}

	public function queryRaw($queryString) {
		throw new UnsupportedMethodException('Can not execute queryRaw in NoSQL');
	}

	public function queryRow(Query $query) {
		throw new UnsupportedMethodException('Can not execute queryRow in NoSQL');
	}

	public function querySet(Query $query) {
		throw new UnsupportedMethodException('Can not execute querySet in NoSQL');
	}

	public function queryColumn(Query $query) {
		throw new UnsupportedMethodException('Can not execute queryColumn in NoSQL');
	}

	public function queryCount(Query $query) {
		throw new UnsupportedMethodException('Can not execute queryCount in NoSQL');
	}

	public function setDbEncoding() {
		throw new UnsupportedMethodException('Can not set encoding in NoSQL');
	}

}
