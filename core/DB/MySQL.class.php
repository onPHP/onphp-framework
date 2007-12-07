<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * MySQL DB connector.
	 * 
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysql
	 * 
	 * @ingroup DB
	**/
	final class MySQL extends Sequenceless
	{
		/**
		 * @return MyDialect
		**/
		public static function getDialect()
		{
			return MyDialect::me();
		}
		
		/**
		 * @return MySQL
		**/
		public function setDbEncoding()
		{
			mysql_query("SET NAMES '{$this->encoding}'", $this->link);
			
			return $this;
		}

		/**
		 * @return MySQL
		**/
		public function connect()
		{
			$this->link =
				$this->persistent
					?
						mysql_pconnect(
							$this->hostname,
							$this->username,
							$this->password,
							// 2 == CLIENT_FOUND_ROWS
							2
						)
					:
						mysql_connect(
							$this->hostname,
							$this->username,
							$this->password,
							true,
							// 2 == CLIENT_FOUND_ROWS
							2
						);
							
			if (
				!$this->link
				|| (
					$this->basename
					&& !mysql_select_db($this->basename, $this->link)
				)
			)
				throw new DatabaseException(
					'can not connect to MySQL server: '.mysql_error($this->link),
					mysql_errno($this->link)
				);
			
			if ($this->encoding)
				$this->setDbEncoding();
			
			return $this;
		}
		
		/**
		 * @return MySQL
		**/
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
			$this->queryNull($query);
			
			return mysql_affected_rows($this->link);
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				return mysql_fetch_assoc($res);
			else
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
			if (!$result = mysql_query($queryString, $this->link)) {
				
				$code = mysql_errno($this->link);
				
				if ($code == 1062)
					$e = 'DuplicateObjectException';
				else
					$e = 'DatabaseException';

				throw new $e(
					mysql_error($this->link).' - '.$queryString,
					$code
				);
			}

			return $result;
		}
		
		public function getTableInfo($table)
		{
			throw new UnimplementedFeatureException();
		}
		
		public function hasQueue()
		{
			return false;
		}
		
		protected function getInsertId()
		{
			return mysql_insert_id($this->link);
		}
		
		private function checkSingle($result)
		{
			if (mysql_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>