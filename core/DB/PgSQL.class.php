<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * PostgreSQL DB connector.
	 * 
	 * @see http://www.postgresql.org/
	 * 
	 * @ingroup DB
	**/
	class PgSQL extends DB
	{
		public static function getDialect()
		{
			return PostgresDialect::me();
		}
		
		public function isBusy()
		{
			return pg_connection_busy($this->link);
		}
		
		public function asyncQuery(Query $query)
		{
			return pg_send_query(
				$this->link, $query->toDialectString($this->getDialect())
			);
		}

		public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		)
		{
			$port = null;
			
			if (strpos($host, ':') !== false)
				list($host, $port) = explode(':', $host, 2);
			
			$conn =
				"host={$host} user={$user}"
				.($pass ? " password={$pass}" : null)
				.($base ? " dbname={$base}" : null)
				.($port ? " port={$port}" : null);

			if ($persistent === true)
				$this->link = pg_pconnect($conn);
			else
				$this->link = pg_connect($conn);

			$this->persistent = $persistent;

			if (!$this->link)
				throw new DatabaseException(
					'can not connect to PostgreSQL server: '.pg_errormessage()
				);

			return $this;
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				pg_close($this->link);

			return $this;
		}
		
		public function isConnected()
		{
			return is_resource($this->link);
		}
		
		/**
		 * misc
		**/
		
		public function obtainSequence($sequence)
		{
			$res = $this->queryRaw("select nextval('{$sequence}') as seq");
			$row = pg_fetch_assoc($res);
			pg_free_result($res);
			return $row['seq'];
		}

		public function setEncoding($encoding)
		{
			return pg_set_client_encoding($this->link, $encoding);
		}
		
		/**
		 * query methods
		**/
		
		public function queryRaw($queryString)
		{
			try {
				return pg_query($this->link, $queryString);
			} catch (BaseException $e) {
				throw new DatabaseException(
					pg_errormessage($this->link).' - '.$queryString
				);
			}
		}

		/**
		 * Same as query, but returns number of affected rows
		 * Returns number of affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			return pg_affected_rows($this->query($query));
		}
		
		public function queryObjectRow(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				if ($row = pg_fetch_assoc($res)) {
					pg_free_result($res);
					return $dao->makeObject($row);
				} else
					pg_free_result($res);
			}

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				$ret = pg_fetch_assoc($res);
				pg_free_result($res);
				return $ret;
			} else
				return null;
		}
		
		public function queryObjectSet(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = pg_fetch_assoc($res))
					$array[] = $dao->makeObject($row);
				
				pg_free_result($res);
				return $array;
			}
			
			return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = pg_fetch_row($res))
					$array[] = $row[0];

				pg_free_result($res);
				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = pg_fetch_assoc($res))
					$array[] = $row;

				pg_free_result($res);
				return $array;
			} else
				return null;
		}
		
		public function supportSequences()
		{
			return true;
		}
		
		private function checkSingle($result)
		{
			if (pg_num_rows($result) > 1)
				throw new TooManyRowsException(
					"query returned too many rows (we need only one)"
				);
			
			return $result;
		}
	}
?>