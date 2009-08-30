<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * DB-connector's implementation basis.
	 * 
	 * @ingroup DB
	**/
	abstract class DB
	{
		const FULL_TEXT_AND		= 1;
		const FULL_TEXT_OR		= 2;

		protected $link			= null;

		protected $persistent	= false;
		
		protected $queueSupported	= true;

		/**
		 * flag to indicate whether we're in transaction
		**/
		private $transaction	= false;
		
		private $queue			= array();
		private $toQueue		= false;
		
		abstract public function connect(
			$user, $pass, $host,
			$base = null, $persistent = false
		);
		abstract public function disconnect();

		abstract public function queryRaw($queryString);

		abstract public function queryRow(Query $query);
		abstract public function queryObjectRow(Query $query, GenericDAO $dao);

		abstract public function querySet(Query $query);
		abstract public function queryObjectSet(Query $query, GenericDAO $dao);

		abstract public function queryColumn(Query $query);
		abstract public function queryCount(Query $query);
		
		abstract public function asyncQuery(Query $query);
		abstract public function isBusy();

		/**
		 * Shortcut, to be forward compatible.
		 * 
		 * @return DB
		**/
		public static function spawn(
			$connector, $user, $pass, $host,
			$base = null, $persistent = false, $encoding = null
		)
		{
			$db = new $connector;
			
			return $db->connect($user, $pass, $host, $base, $persistent);
		}
		
		public function __destruct()
		{
			if ($this->isConnected()) {
				if ($this->transaction)
					$this->rollback();

				if (!$this->persistent)
					$this->disconnect();
			}
		}
		
		public static function getDialect()
		{
			throw new UnimplementedFeatureException('implement me, please');
		}
		
		/**
		 * transaction handling
		 * @deprecated by Transaction class
		**/

		public function begin($level = null, $mode = null)
		{
			$begin = 'begin';
			
			if ($level && $level instanceof IsolationLevel)
				$begin .= ' '.$level->toString();
			
			if ($mode && $mode instanceof AccessMode)
				$begin .= ' '.$mode->toString();

			if ($this->toQueue)
				$this->queue[] = $begin;
			else
				$this->queryRaw("{$begin};\n");
			
			$this->transaction = true;
			
			return $this;
		}
		
		public function commit()
		{
			if ($this->toQueue)
				$this->queue[] = "commit;";
			else
				$this->queryRaw("commit;\n");
			
			$this->transaction = false;
			
			return $this;
		}
		
		public function rollback()
		{
			if ($this->toQueue)
				$this->queue[] = "rollback;";
			else
				$this->queryRaw("rollback;\n");
			
			$this->transaction = false;
			
			return $this;
		}
		
		public function inTransaction()
		{
			return $this->transaction;
		}
		
		/**
		 * queue handling
		 * @deprecated by Queue class
		**/

		public function queueStart()
		{
			if ($this->queueSupported)
				$this->toQueue = true;
			
			return $this;
		}
		
		public function queueStop()
		{
			$this->toQueue = false;
			
			return $this;
		}
		
		public function queueDrop()
		{
			$this->queue = array();
			
			return $this;
		}
		
		public function queueFlush()
		{
			if ($this->queue)
				$this->queryRaw(
					implode(";\n", $this->queue)
				);
			
			$this->toQueue = false;
			
			return $this->queueDrop();
		}
		
		/**
		 * base queries
		**/
		
		public function query(Query $query)
		{
			return $this->queryRaw($query->toDialectString($this->getDialect()));
		}

		public function queryNull(Query $query)
		{
			if ($query instanceof SelectQuery)
				throw new WrongArgumentException(
					'only non-select queries supported'
				);
			
			if ($this->toQueue) {
				$this->queue[] = $query->toDialectString($this->getDialect());
				return true;
			} else
				return $this->query($query);
		}
		
		public function isConnected()
		{
			return is_resource($this->link);
		}
		
		public function supportSequences()
		{
			return false;
		}

		public function isPersistent()
		{
			return $this->persistent;
		}
	}
?>