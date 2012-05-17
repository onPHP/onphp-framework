<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
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
		
		// credentials
		protected $username	= null;
		protected $password	= null;
		protected $hostname	= null;
		protected $port		= null;
		protected $basename	= null;
		protected $encoding	= null;
		
		/**
		 * flag to indicate whether we're in transaction
		**/
		private $transaction	= false;
		/**
		 * @var list of all started savepoints
		 */
		private $savepointList	= array();
		
		private $queue			= array();
		private $toQueue		= false;
		
		abstract public function connect();
		abstract public function disconnect();
		
		abstract public function getTableInfo($table);

		abstract public function queryRaw($queryString);

		abstract public function queryRow(Query $query);
		abstract public function querySet(Query $query);
		abstract public function queryColumn(Query $query);
		abstract public function queryCount(Query $query);
		
		// actually set's encoding
		abstract public function setDbEncoding();

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
		 * Shortcut.
		 * 
		 * @return DB
		**/
		public static function spawn(
			$connector, $user, $pass, $host,
			$base = null, $persistent = false, $encoding = null
		)
		{
			$db = new $connector;
			
			$db->
				setUsername($user)->
				setPassword($pass)->
				setHostname($host)->
				setBasename($base)->
				setPersistent($persistent)->
				setEncoding($encoding);
			
			return $db;
		}
		
		public function getLink()
		{
			return $this->link;
		}
		
		/**
		 * transaction handling
		 * @deprecated by Transaction class
		**/
		//@{
		/**
		 * @return DB
		**/
		public function begin(
			/* IsolationLevel */ $level = null,
			/* AccessMode */ $mode = null
		)
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
		
		/**
		 * @return DB
		**/
		public function commit()
		{
			if ($this->toQueue)
				$this->queue[] = 'commit;';
			else
				$this->queryRaw("commit;\n");
			
			$this->transaction = false;
			$this->savepointList = array();
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function rollback()
		{
			if ($this->toQueue)
				$this->queue[] = 'rollback;';
			else
				$this->queryRaw("rollback;\n");
			
			$this->transaction = false;
			$this->savepointList = array();
			
			return $this;
		}
		
		public function inTransaction()
		{
			return $this->transaction;
		}
		//@}
		
		/**
		 * queue handling
		 * @deprecated by Queue class
		**/
		//@{
		/**
		 * @return DB
		**/
		public function queueStart()
		{
			if ($this->hasQueue())
				$this->toQueue = true;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function queueStop()
		{
			$this->toQueue = false;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function queueDrop()
		{
			$this->queue = array();
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function queueFlush()
		{
			if ($this->queue)
				$this->queryRaw(
					implode(";\n", $this->queue)
				);
			
			$this->toQueue = false;
			
			return $this->queueDrop();
		}
		
		public function isQueueActive()
		{
			return $this->toQueue;
		}
		//@}
		
		/**
		 * @param string $savepointName
		 * @return DB 
		 */
		public function savepointBegin($savepointName)
		{
			if (!$this->inTransaction())
				throw new DatabaseException('To use savepoint begin transaction first');
			
			$query = 'savepoint '.$savepointName;
			if ($this->toQueue)
				$this->queue[] = $query;
			else
				$this->queryRaw("{$query};\n");
				
			return $this->addSavepoint($savepointName);
		}
		
		/**
		 * @param string $savepointName
		 * @return DB 
		 */
		public function savepointRelease($savepointName)
		{
			if (!$this->inTransaction())
				throw new DatabaseException('To release savepoint begin transaction first');
			
			if (!$this->checkSavepointExist($savepointName))
				throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");
			
			$query = 'release savepoint '.$savepointName;
			if ($this->toQueue)
				$this->queue[] = $query;
			else
				$this->queryRaw("{$query};\n");
				
			return $this->dropSavepoint($savepointName);
		}
		
		/**
		 * @param string $savepointName
		 * @return DB 
		 */
		public function savepointRollback($savepointName)
		{
			if (!$this->inTransaction())
				throw new DatabaseException('To rollback savepoint begin transaction first');
			
			if (!$this->checkSavepointExist($savepointName))
				throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");
			
			$query = 'rollback to savepoint '.$savepointName;
			if ($this->toQueue)
				$this->queue[] = $query;
			else
				$this->queryRaw("{$query};\n");
				
			return $this->dropSavepoint($savepointName);
		}
		
		/**
		 * base queries
		**/
		//@{
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
		//@}
		
		public function isConnected()
		{
			return is_resource($this->link);
		}
		
		public function hasSequences()
		{
			return false;
		}
		
		public function hasQueue()
		{
			return true;
		}

		public function isPersistent()
		{
			return $this->persistent;
		}
		
		/**
		 * @return DB
		**/
		public function setPersistent($really = false)
		{
			$this->persistent = ($really === true);
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function setUsername($name)
		{
			$this->username = $name;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function setPassword($password)
		{
			$this->password = $password;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function setHostname($host)
		{
			$port = null;
			
			if (strpos($host, ':') !== false)
				list($host, $port) = explode(':', $host, 2);
			
			$this->hostname = $host;
			$this->port = $port;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function setBasename($base)
		{
			$this->basename = $base;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function setEncoding($encoding)
		{
			$this->encoding = $encoding;
			
			return $this;
		}
		
		/**
		 * @param string $savepointName 
		 * @return DB
		 */
		private function addSavepoint($savepointName)
		{
			if ($this->checkSavepointExist($savepointName))
				throw new DatabaseException("savepoint with name '{$savepointName}' already marked");
				
			$this->savepointList[$savepointName] = true;
			return $this;
		}
		
		/**
		 * @param string $savepointName 
		 * @return DB
		 */
		private function dropSavepoint($savepointName)
		{
			if (!$this->checkSavepointExist($savepointName))
				throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");
				
			unset($this->savepointList[$savepointName]);
			return $this;
		}
		
		private function checkSavepointExist($savepointName)
		{
			return isset($this->savepointList[$savepointName]);
		}
	}
?>