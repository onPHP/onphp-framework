<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Sveta A. Smirnova                          *
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
	 * @deprecated
	 * @see http://www.mysql.com/
	 * @see http://www.php.net/mysql
	 *
	 * @ingroup DB
	 * @deprecated use MySQLim
	**/
	final class MySQL extends BaseMySQL
	{
		protected $aliases = array(
			'close'			=> 'mysql_close',
			'ping'			=> 'mysql_ping',
			'affected_rows'	=> 'mysql_affected_rows',
			'fetch_assoc'	=> 'mysql_fetch_assoc',
			'fetch_row'		=> 'mysql_fetch_row',
			'query'			=> 'mysql_query',
			'errno'			=> 'mysql_errno',
			'error'			=> 'mysql_error',
			'insert_id'		=> 'mysql_insert_id',
			'num_rows'		=> 'mysql_num_rows',
		);

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
			$hostname =
				$this->port
					? $this->hostname.':'.$this->port
					: $this->hostname;

			$this->link =
				$this->persistent
					?
						mysql_pconnect(
							$hostname,
							$this->username,
							$this->password,
							// 2 == CLIENT_FOUND_ROWS
							2
						)
					:
						mysql_connect(
							$hostname,
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
		 * @return MyDialect
		**/
		protected function spawnDialect()
		{
			return new MyDialect();
		}
	}
?>