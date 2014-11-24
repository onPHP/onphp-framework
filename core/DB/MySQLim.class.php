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
	namespace Onphp;

	final class MySQLim extends Sequenceless
	{
		private $needAutoCommit = false;
		private $defaultEngine;

		/**
		 * @return \Onphp\MySQLim
		**/
		public function setDbEncoding()
		{
			mysqli_set_charset($this->link, $this->encoding);
			
			return $this;
		}

		/**
		 * @param $flag
		 * @return \Onphp\MySQLim
		**/
		public function setNeedAutoCommit($flag)
		{
			$this->needAutoCommit = $flag == true;
			$this->setupAutoCommit();
			return $this;
		}

		/**
		 * @param string $engine
		 * @return MySQLim
		 */
		public function setDefaultEngine($engine)
		{
			$this->defaultEngine = $engine;
			$this->setupDefaultEngine();
			return $this;
		}

		/**
		 * @return $this
		 * @throws \Onphp\DatabaseException
		 * @throws \Onphp\UnsupportedMethodException
		 */
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

			$this->setupAutoCommit();
			$this->setupDefaultEngine();
			
			return $this;
		}
		
		/**
		 * @return \Onphp\MySQLim
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				mysqli_close($this->link);

			return $this;
		}
		
		public function isConnected()
		{
			return (parent::isConnected() || $this->link instanceof \mysqli)
				&& mysqli_ping($this->link);
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
					$e = '\Onphp\DuplicateObjectException';
				else
					$e = '\Onphp\DatabaseException';
				
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
		
		/**
		 * @return \Onphp\MyImprovedDialect
		**/
		protected function spawnDialect()
		{
			return new MyImprovedDialect();
		}
		
		private function checkSingle($result)
		{
			if (mysqli_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}

		private function setupAutoCommit()
		{
			if ($this->isConnected()) {
				mysqli_autocommit($this->link, $this->needAutoCommit);
			}
		}

		private function setupDefaultEngine()
		{
			if ($this->defaultEngine && $this->isConnected()) {
				mysqli_query($this->link, 'SET storage_engine='.$this->defaultEngine);
			}
		}
	}
?>