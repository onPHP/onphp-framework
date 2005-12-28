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

	// FIXME: too many features not implemented
	class MSSQL extends DB
	{
		private static $dialect = null;
		
		public function __construct()
		{
			self::$dialect = new MSDialect();
		}
		
		public static function getDialect()
		{
			return self::$dialect;
		}
		
		public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		)
		{
			$this->link =
				($persistent)
					? mssql_pconnect($host, $user, $pass)
					: mssql_connect($host, $user, $pass);
							
			if (!$this->link || ($base && !mssql_select_db($base, $this->link)))
				throw new DatabaseException(
					'can not connect to MSSQL server: ' . mssql_get_last_message()
				);
			
			$this->persistent = $persistent;
			
			return $this;
		}
		
		public function asyncQuery(Query $query)
		{
			throw new UnsupportedMethodException;
		}

		public function isBusy()
		{
			throw new UnsupportedMethodException;
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				mssql_close($this->link);

			return $this;
		}
		
		/**
		 * Same as query, but returns number of affected rows
		 * 
		 * Use it for returns number of affected rows in insert/update queries
		 * @param	Query
		 * @access	public
		 * @return	integer
		**/
		public function queryCount(Query $query)
		{
			return mssql_rows_affected($this->query($query));
		}
		
		public function queryObjectRow(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				if ($row = mssql_fetch_assoc($res))
					return $dao->makeObject($row);

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res))
				return mssql_fetch_assoc($res);
			else
				return null;
		}
		
		public function queryObjectSet(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = mssql_fetch_assoc($res))
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

				while ($row = mssql_fetch_row($res))
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

				while ($row = mssql_fetch_assoc($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function queryRaw($queryString)
		{
			if (!$result = mssql_query($queryString, $this->link))
				throw new DatabaseException(
					"failed to execute such query - '{$queryString}': ".
					mssql_get_last_message()
				);

			return $result;
		}
		
		public function obtainSequence($sequence)
		{
			$res = $this->queryRow(OSQL::select()->getFunction('nextval', $sequence, 'seq'));

			return $res['seq'];
		}

		public function setEncoding($encoding)
		{
			throw new UnsupportedMethodException();
		}
		
		private function checkSingle($result)
		{
			if (mssql_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>