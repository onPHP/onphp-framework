<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Interbase DB connector.
	 *
	 * @see http://firebird.sourceforge.net/
	**/
	class IBase extends DB
	{
		private static $dialect = null;
		
		public function __construct()
		{
			self::$dialect = new InterbaseDialect();
		}
		
		public static function getDialect()
		{
			return self::$dialect;
		}
		
		public function isBusy()
		{
			throw new UnsupportedMethodException();
		}
		
		public function asyncQuery(Query $query)
		{
			throw new UnsupportedMethodException();
		}

		public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		)
		{
			$port = null;
			
			if (strpos($host, ':') !== false)
				list($host, $port) = explode(':', $host, 2);

			if ($persistent === true)
				$conn = 'ibase_pconnect';
			else
				$conn = 'ibase_connect';
			
			$this->link = $conn(
				"{$host}:{$base}", $user, $pass, DEFAULT_ENCODING
			);

			$this->persistent = $persistent;

			if (!$this->link)
				throw new DatabaseException(
					'can not connect to PostgreSQL server: '.ibase_errmsg(),
					ibase_errcode()
				);

			return $this;
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				ibase_close($this->link);

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
			return ibase_gen_id($sequence, 1);
		}

		public function setEncoding($encoding)
		{
			// already set by connect()
			throw new UnsupportedMethodException();
		}
		
		/**
		 * query methods
		**/
		
		public function queryRaw($queryString)
		{
			//	echo $queryString.'<hr>'; flush();
			//	error_log($queryString);
			try {
				return ibase_query($this->link, $queryString);
			} catch (BaseException $e) {
				throw new DatabaseException(
					ibase_errmsg(),
					ibase_errcode()
				);
			}
		}

		/**
		 * Same as query, but returns number of affected rows
		 * Returns number of affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			return ibase_affected_rows($this->query($query));
		}
		
		public function queryObjectRow(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				if ($row = ibase_fetch_assoc($res, IBASE_TEXT)) {
					ibase_free_result($res);
					
					return $dao->makeObject(
						$row = array_change_key_case($row, CASE_LOWER)
					);
				} else 
					ibase_free_result($res);
			}

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				$ret = ibase_fetch_assoc($res, IBASE_TEXT);
				ibase_free_result($res);
				return array_change_key_case($ret, CASE_LOWER);
			} else
				return null;
		}
		
		public function queryObjectSet(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = ibase_fetch_assoc($res, IBASE_TEXT))
					$array[] = $dao->makeObject(
						$row = array_change_key_case($row, CASE_LOWER)
					);
				
				ibase_free_result($res);
				return $array;
			}
			
			return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = ibase_fetch_row($res))
					$array[] = current($row);

				ibase_free_result($res);
				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = ibase_fetch_assoc($res, IBASE_TEXT))
					$array[] = array_change_key_case($row, CASE_LOWER);

				ibase_free_result($res);
				return $array;
			} else
				return null;
		}
		
		private function checkSingle($result)
		{
			if (ibase_num_rows($result) > 1)
				throw new TooManyRowsException(
					"query returned too many rows (we need only one)"
				);
			
			return $result;
		}
	}
?>