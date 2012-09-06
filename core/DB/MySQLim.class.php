<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * MySQL DB connector.
	 * 
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysqli
	 * 
	 * @ingroup DB
	**/
	final class MySQLim extends Sequenceless
	{
		/**
		 * @return MyImprovedDialect
		**/
		public static function getDialect()
		{
			return MyImprovedDialect::me();
		}
		
		/**
		 * @return MySQLim
		**/
		public function setDbEncoding()
		{
			mysqli_set_charset($this->link, $this->encoding);
			
			return $this;
		}

		/**
		 * @return MySQLim
		**/
		public function connect()
		{
			if ($this->persistent)
				throw new UnsupportedMethodException();
			
			$this->link = mysqli_init();
			
			try {
				mysqli_real_connect(
					$this->link,
					$this->hostname,
					$this->username,
					$this->password,
					$this->basename,
					$this->port,
					null,
					MYSQLI_CLIENT_FOUND_ROWS
				);
			} catch (BaseException $e) {
				throw new DatabaseException(
					'can not connect to MySQL server: '.$e->getMessage()
				);
			}
			
			if ($this->encoding)
				$this->setDbEncoding();
			
			return $this;
		}
		
		/**
		 * @return MySQLim
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				mysqli_close($this->link);

			return $this;
		}
		
		/**
		 * Same as query, but returns number of
		 * affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			$this->queryNull($query);
			
			return mysqli_affected_rows($this->link);
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				return mysqli_fetch_assoc($res);
			else
				return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = mysqli_fetch_row($res))
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

				while ($row = mysqli_fetch_assoc($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function queryRaw($queryString)
		{
			if (!$result = mysqli_query($this->link, $queryString)) {
				
				$code = mysqli_errno($this->link);
				
				if ($code == 1062)
					$e = 'DuplicateObjectException';
				else
					$e = 'DatabaseException';
				
				throw new $e(
					mysqli_error($this->link).' - '.$queryString,
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
			return mysqli_insert_id($this->link);
		}
		
		private function checkSingle($result)
		{
			if (mysqli_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>