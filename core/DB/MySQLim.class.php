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
	final class MySQLim extends BaseMySQL
	{
		private $needAutoCommit = false;

		protected $aliases = array(
			'close'			=> 'mysqli_close',
			'ping'			=> 'mysqli_ping',
			'affected_rows'	=> 'mysqli_affected_rows',
			'fetch_assoc'	=> 'mysqli_fetch_assoc',
			'fetch_row'		=> 'mysqli_fetch_row',
			'query'			=> 'mysqli_query',
			'errno'			=> 'mysqli_errno',
			'error'			=> 'mysqli_error',
			'insert_id'		=> 'mysqli_insert_id',
			'num_rows'		=> 'mysqli_num_rows',
		);

		/**
		 * @return MySQLim
		**/
		public function setDbEncoding()
		{
			mysqli_set_charset($this->link, $this->encoding);

			return $this;
		}

		/**
		 * @param $flag
		 * @return MySQLim
		**/
		public function setNeedAutoCommit($flag)
		{
			$this->needAutoCommit = $flag == true;
			$this->setupAutoCommit();
			return $this;
		}

		/**
		 * @return $this
		 * @throws DatabaseException
		 * @throws UnsupportedMethodException
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

			return $this;
		}

		/**
		 * @return MyImprovedDialect
		**/
		protected function spawnDialect()
		{
			return new MyImprovedDialect();
		}

		private function setupAutoCommit()
		{
			if ($this->isConnected()) {
				mysqli_autocommit($this->link, $this->needAutoCommit);
			}
		}
	}
?>