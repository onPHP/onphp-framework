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
	class MySQL extends DB
	{
		private static $dialect = null;
		
		public function __construct()
		{
			self::$dialect = new MyDialect();
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
					? mysql_pconnect($host, $user, $pass)
					: mysql_connect($host, $user, $pass);
							
			if (!$this->link || ($base && !mysql_select_db($base, $this->link)))
				throw new DatabaseException(
					'can not connect to MySQL server: '.mysql_error($this->link)
				);
			
			$this->persistent = $persistent;
			
			return $this;
		}
		
		public function asyncQuery(Query $query)
		{
			throw new UnimplementedFeatureException();
		}

		public function isBusy()
		{
			throw new UnimplementedFeatureException();
		}
		
		public function disconnect()
		{
			if ($this->isConnected())
				mysql_close($this->link);

			return $this;
		}
		
		// FIXME: use parent's query()
		public function query(Query $query)
		{
			//	echo $query->toString($this).'<hr>'; flush();
			//	echo $query->toString($this)."\n"; flush();
			
			if (!($result = mysql_query($query->toString($this->getDialect()), $this->link))) {
				throw new DatabaseException(
					"failed to execute such query - '{$query->toString($this->getDialect())}': ".
					mysql_error($this->link)
				);
			}

			return $result;
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
			return mysql_affected_rows($this->query($query));
		}
		
		public function queryObjectRow(Query $query, CommonDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res)
				if ($row = mysql_fetch_assoc($res))
					return $dao->makeObject($row);

			return null;
		}
		
		public function queryRow(Query $query)
		{
			$res = $this->query($query);
			
			if ($res)
				return mysql_fetch_assoc($res);
			else
				return null;
		}
		
		public function queryObjectSet(Query $query, CommonDAO $dao)
		{
			$res = $this->query($query);
			
			if ($res) {
				$array = array();
				
				while ($row = mysql_fetch_assoc($res))
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

				while ($row = mysql_fetch_row($res))
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

				while ($row = mysql_fetch_assoc($res))
					$array[] = $row;

				return $array;
			} else
				return null;
		}
		
		public function queryRaw($queryString)
		{
			if (!$result = mysql_query($this->link, $queryString))
				throw new DatabaseException(
					"failed to execute such query - '{$queryString}': ".
					mysql_error($this->link)
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
	}
?>