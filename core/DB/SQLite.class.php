<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * SQLite DB connector.
	 *
	 * @see http://www.sqlite.org/
	 * 
	 * @ingroup DB
	**/
	final class SQLite extends Sequenceless
	{
		/**
		 * @return LiteDialect
		**/
		public static function getDialect()
		{
			return LiteDialect::me();
		}

		/**
		 * @return SQLite
		**/
		public function connect()
		{
			if ($this->persistent)
				$this->link = sqlite_popen($this->basename);
			else 
				$this->link = sqlite_open($this->basename);

			if (!$this->link)
				throw new DatabaseException(
					'can not open SQLite base: '
					.sqlite_error_string(sqlite_last_error($this->link))
				);

			return $this;
		}
		
		/**
		 * @return SQLite
		**/
		public function disconnect()
		{
			if ($this->isConnected())
				sqlite_close($this->link);

			return $this;
		}
		
		public function isConnected()
		{
			return is_resource($this->link);
		}
		
		/**
		 * misc
		**/
		
		public function setDbEncoding()
		{
			throw new UnsupportedMethodException();
		}
		
		/**
		 * query methods
		**/
		
		public function queryRaw($queryString)
		{
			try {
				return sqlite_query($queryString, $this->link);
			} catch (BaseException $e) {
				$code = sqlite_last_error($this->link);
				
				if ($code == 19)
					$e = 'DuplicateObjectException';
				else
					$e = 'DatabaseException';
					
				throw new $e(
					sqlite_error_string($code).' - '.$queryString,
					$code
				);
			}
		}

		/**
		 * Same as query, but returns number of affected rows
		 * Returns number of affected rows in insert/update queries
		**/
		public function queryCount(Query $query)
		{
			return sqlite_changes($this->query($query));
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($this->checkSingle($res)) {
				$names = $query->getFieldNames();
				$width = count($names);
				$assoc = array();

				$row = sqlite_fetch_array($res, SQLITE_NUM);
				
				for ($i = 0; $i < $width; ++$i)
					$assoc[$names[$i]] = $row[$i];
				
				return $assoc;
			}
			else
				return null;
		}
		
		public function queryColumn(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();

				while ($row = sqlite_fetch_single($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function querySet(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				$names = $query->getFieldNames();
				$width = count($names);
				
				while ($row = sqlite_fetch_array($res, SQLITE_NUM)) {
					$assoc = array();
					
					for ($i = 0; $i < $width; ++$i)
						$assoc[$names[$i]] = $row[$i];
					
					$array[] = $assoc;
				}

				return $array;
			} else
				return null;
		}
		
		public function getTableInfo($table)
		{
			throw new UnimplementedFeatureException();
		}
		
		protected function getInsertId()
		{
			return sqlite_last_insert_rowid($this->link);
		}
		
		private function checkSingle($result)
		{
			if (sqlite_num_rows($result) > 1)
				throw new TooManyRowsException(
					'query returned too many rows (we need only one)'
				);
			
			return $result;
		}
	}
?>