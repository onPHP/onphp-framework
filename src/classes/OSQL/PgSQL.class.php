<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PgSQL extends DB
	{
		private $tsConfiguration = 'default_russian';

		public function getTsConfiguration()
		{
			return $this->tsConfiguration;
		}

		public function setTsConfiguration($configuration)
		{
			$this->tsConfiguration = $configuration;
		}

		public function isBusy()
		{
			return pg_connection_busy($this->link);
		}
		
		public function asyncQuery(Query $query)
		{
			// since we're not interesting in results - stfu it
			try {
				return pg_send_query($this->link, $query->toString($this));
			} catch (BaseException $e) {
				return true;
			}
		}

		public function connect($user, $pass, $host, $base = null, $persistent = false)
		{
			$conn = "host={$host} user={$user}".
				($pass ? " password={$pass}" : '').
				($base ? " dbname={$base}" : '');

			if ($persistent)
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
		
		public function begin()
		{
			$this->queryRaw('BEGIN;');
			$this->transaction = true;
			
			return $this;
		}
		
		public function commit()
		{
			$this->queryRaw('COMMIT;');
			$this->transaction = false;
			
			return $this;
		}
		
		public function rollback()
		{
			$this->queryRaw('ROLLBACK;');
			$this->transaction = false;
			
			return $this;
		}
		
		public function query(Query $query)
		{
			//	echo $query->toString($this).'<hr>'; flush();
			//	echo $query->toString($this)."\n";
			//	MiscUtils::el($query->toString($this), __LINE__);
			
			if ($this->toQueue)
				while (pg_get_result($this->link)) {
					// do nothing
				}
			
			return $this->queryRaw($query->toString($this));
		}

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
		 *
		 * @param	Query
		 * @access	public
		 * @return	integer
		**/
		public function queryCount(Query $query)
		{
			return pg_affected_rows($this->query($query));
		}
		
		public function queryObjectRow(Query $query, CommonDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res)
				if ($row = pg_fetch_assoc($res)) {
					pg_free_result($res);
					return $dao->makeObject($row);
				} else 
					pg_free_result($res);

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($res) {
				$ret = pg_fetch_assoc($res);
				pg_free_result($res);
				return $ret;
			} else
				return null;
		}
		
		public function queryObjectSet(Query $query, CommonDAO $dao)
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
		
		public function obtainSequence($sequence)
		{
			$res = $this->queryRaw("select nextval('$sequence') as seq");
			$row = pg_fetch_assoc($res);
			pg_free_result($res);
			return $row['seq'];
		}

		public function setEncoding($encoding)
		{
			return pg_set_client_encoding($this->link, $encoding);
		}

		public function quoteValue($value)
		{
			// to avoid values like '108E102' (is_numeric()'ll return true)
			return ((is_numeric($value) && $value == (int) $value && strlen($value) == strlen((int) $value))
						|| strtolower($value) == 'null')
					? $value
					: "'".addslashes($value)."'";
		}
		
		public function quoteField($field)
		{
			if (strpos($field, '.'))
				throw new WrongArgumentException();
			elseif (strpos($field, '::')) {
				list ($field, $cast) = explode('::', $field, 2);
				return "\"{$field}\"::{$cast}";
			}
			
			return $this->quoteTable($field);
		}
		
		public function quoteTable($table)
		{
			return "\"{$table}\"";
		}

		public function fullTextSearch($field, $words, $logic)
		{
			Assert::isArray($words);
			
			$glue = ($logic == DB::FULL_TEXT_AND) ? ' & ' : ' | ';
			$searchString = 
				strtolower(
					implode(
						$glue, 
						array_map(
							array(&$this, 'quoteValue'), 
							$words
						)
					)
				);

			return 
				'('.$this->fieldToString($field).
				" @@ to_tsquery('{$this->tsConfiguration}', ".
				$this->quoteValue($searchString)."))";
		}
	}
?>