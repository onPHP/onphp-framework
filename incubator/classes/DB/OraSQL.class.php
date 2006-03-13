<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class OraSQL extends DB
	{
		private static $dialect = null;
		
		public function __construct()
		{
			self::$dialect = new OracleDialect();
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

		public function queryCount(Query $query)
		{
			throw new UnsupportedMethodException();
		}

		public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		)
		{
			if ($persistent === true)
				$this->link = oci_pconnect($user, $pass, $base, DEFAULT_ENCODING);
			else 
				$this->link = oci_connect($user, $pass, $base, DEFAULT_ENCODING);

			$this->persistent = ($persistent === true);

			if (!$this->link)
				throw new DatabaseException(
					'can not connect to Oracle server: '.oci_error()
				);

			return $this;
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				oci_close($this->link);

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
			$row = oci_fetch_assoc($res);
			oci_free_statement($res);
			return $row['seq'];
		}

		public function setEncoding($encoding)
		{
			// already set by connect()
			return true;
		}
		
		/**
		 * query methods
		**/
		
		public function queryRaw($queryString)
		{
			try {
				$stmt = oci_parse($this->link, $queryString);
				oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
				return $stmt;
			} catch (BaseException $e) {
				throw new DatabaseException(
					oci_error($stmt).' - '.$queryString
				);
			}
		}

		public function queryObjectRow(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				if (oci_num_rows($res) > 1)
					throw new DatabaseException(
						"query returned too many rows (we need only one) : "
						.$query->toDialectString($this->getDialect())
					);

				if ($row = oci_fetch_assoc($res)) {
					oci_free_statement($res);
					return $dao->makeObject($row);
				} else 
					oci_free_statement($res);
			}

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				$ret = oci_fetch_assoc($res);
				oci_free_statement($res);
				return $ret;
			} else
				return null;
		}
		
		public function queryObjectSet(Query $query, GenericDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = oci_fetch_assoc($res))
					$array[] = $dao->makeObject($row);
				
				oci_free_statement($res);
				return $array;
			}
			
			return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = oci_fetch_row($res))
					$array[] = $row[0];

				oci_free_statement($res);
				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = oci_fetch_assoc($res))
					$array[] = $row;

				oci_free_statement($res);
				return $array;
			} else
				return null;
		}
		
		private function checkSingle($result)
		{
			if (oci_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>