<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * MySQL DB connector.
	 * 
	 * You should follow two conventions, when stornig objects thru this one:
	 * 
	 * 1) objects should be childs of IdentifiableObject;
	 * 2) sequence name should equal table name + '_id'.
	 *
	 * @see IdentifiableOjbect
	 * @link http://www.mysql.com/
	**/
	class MySQL extends DB
	{
		private $sequencePool = array();
		
		private static $dialect = null;
		
		public function __construct()
		{
			self::$dialect = new MyDialect();
		}
		
		public static function getDialect()
		{
			return self::$dialect;
		}
		
		public function asyncQuery(Query $query)
		{
			throw new UnsupportedMethodException();
		}

		public function isBusy()
		{
			throw new UnsupportedMethodException();
		}

		public function setEncoding($encoding)
		{
			throw new UnsupportedMethodException();
		}

		public function obtainSequence($sequence)
		{
			$id = Identifier::create();
			
			$this->sequencePool[$sequence][] = $id;
			
			return $id;
		}

		public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		)
		{
			$this->link =
				($persistent)
					? mysql_pconnect($host, $user, $pass)
					: mysql_connect($host, $user, $pass);
							
			if (!$this->link || ($base && !mysql_select_db($base, $this->link)))
				throw new DatabaseException(
					'can not connect to MySQL server: '.mysql_error($this->link),
					mysql_errno($this->link)
				);
			
			$this->persistent = $persistent;
			
			return $this;
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				mysql_close($this->link);

			return $this;
		}
		
		public function query(Query $query)
		{
			$result = $this->queryRaw($query->toString($this->getDialect()));
			
			if (
				($query instanceof InsertQuery)
				&& isset($this->sequencePool[$name = $query->getTable().'_id'])
			) {
				$id = current($this->sequencePool[$name]);
				
				$id->setId(mysql_insert_id($this->link))->finalize();
				
				unset(
					$this->sequencePool[
						$name
					][
						key($this->sequencePool)
					]
				);
			}
			
			return $result;
		}

		/**
		 * Same as query, but returns number of
		 * affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			return mysql_affected_rows($this->query($query));
		}
		
		public function queryObjectRow(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				if ($row = mysql_fetch_assoc($res))
					return $dao->makeObject($row);

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				return mysql_fetch_assoc($res);
			else
				return null;
		}
		
		public function queryObjectSet(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = mysql_fetch_assoc($res))
					$array[] = $dao->makeObject($row);

				return $array;
			}
			
			return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = mysql_fetch_row($res))
					$array[] = $row[0];

				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = mysql_fetch_assoc($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function queryRaw($queryString)
		{
			if (!$result = mysql_query($queryString, $this->link))
				throw new DatabaseException(
					"failed to execute such query - '{$queryString}': ".
					mysql_error($this->link),
					mysql_errno($this->link)
				);

			return $result;
		}
		
		private function checkSingle($result)
		{
			if (mysql_num_rows($result) > 1)
				throw new DatabaseException(
					"query returned too many rows (we need only one): "
					.$query->toString($this->getDialect())
				);
			
			return $result;
		}
	}
?>