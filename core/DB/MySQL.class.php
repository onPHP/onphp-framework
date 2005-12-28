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
	 * @see http://www.mysql.com/
	**/
	class MySQL extends Sequenceless
	{
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
		
		protected function getInsertId()
		{
			return mysql_insert_id($this->link);
		}
		
		private function checkSingle($result)
		{
			if (mysql_num_rows($result) > 1)
				throw new DatabaseException(
					"query returned too many rows (we need only one)"
				);
			
			return $result;
		}
	}
?>