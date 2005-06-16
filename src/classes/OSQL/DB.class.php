<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class DB
	{
		const FULL_TEXT_AND		= 1;
		const FULL_TEXT_OR		= 2;

		protected $link			= null;

		protected $queue		= array();
		protected $toQueue		= false;

		protected $persistent	= false;
		protected $transaction	= false; // flag to indicate current transaction
		
		abstract public function connect($user, $pass, $host,
										$base = null, $persistent = false);
		abstract public function disconnect();
		
		abstract public function query(Query $query);
		abstract public function queryRaw($queryString);

		abstract public function queryRow(Query $query);
		abstract public function queryObjectRow(Query $query, CommonDAO $dao);

		abstract public function querySet(Query $query);
		abstract public function queryObjectSet(Query $query, CommonDAO $dao);
		
		abstract public function begin();
		abstract public function commit();
		abstract public function rollback();

		abstract public function quoteValue($value);
		abstract public function quoteField($field);
		abstract public function quoteTable($table);
		
		abstract public function asyncQuery(Query $query);
		abstract public function isBusy();

		abstract public function fullTextSearch($field, $words, $logic);

		public function __destruct()
		{
			if (!$this->persistent)
				$this->disconnect();
		}
		
		public function queryNull(Query $query)
		{
			if ($query instanceof SelectQuery)
				throw new UnsupportedMethodException(
					'only non-select queries supported'
				);

			if ($this->toQueue && !$this->transaction) {
				if ($this->isBusy() || !$this->asyncQuery($query))
					$this->queue[] = $query->toString($this);

				return true;
			} else
				return $this->query($query);
		}
		
		public function objectQuery(SelectQuery $query, CommonDAO $dao)
		{
			$list = $this->queryObjectSet($query, $dao);
			
			$count = clone $query;
			
			$count =
				$this->queryRow(
					$count->dropFields()->dropOrder()->
					getCount('*', 'count')->limit(null, null)
				);

			$res = new QueryResult();

			return
				$res->
					setList($list)->
					setCount($count['count'])->
					setQuery($query);
		}

		public function isConnected()
		{
			return is_resource($this->link);
		}

		public function queueStart()
		{
			$this->toQueue = true;
			
			return $this;
		}
		
		public function queueStop()
		{
			$this->toQueue = false;
			
			return $this;
		}
		
		public function queueFlush()
		{
			while (pg_get_result($this->link)) {
				// do nothing
			}
			
			$this->queryRaw(implode('; ', $this->queue));

			$this->queue = array();
			
			return true;
		}
		
		public function setPersistent($persistent)
		{
			$this->persistent = $persistent;
			
			return $this;
		}
		
		public function getPersistent()
		{
			return $this->persistent;
		}
		
		public function valueToStirg($value)
		{
			return
				$value instanceof DBValue
					? $value->toString($this)
					: $this->quoteValue($value);
		}
		
		public function fieldToString($field)
		{
			return
				$field instanceof DBField
					? $field->toString($this)
					: $this->quoteField($field);
		}
	}
?>