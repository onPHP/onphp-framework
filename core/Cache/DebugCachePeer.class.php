<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * CachePeer for debugging and logging puproses.
	 *
	 * @ingroup Cache
	**/
	final class DebugCachePeer extends SelectivePeer
	{
		private $peer				= null;
		private $logger				= null;
		private $isWeb				= true;
		private $whiteListActions	= array();
		private $blackListActions	= array();
		private $actionFilter		= false;
		
		
		/**
		 * @return DebugCachePeer
		**/
		public static function create(CachePeer $peer, $logfile, $isWeb = true)
		{
			return new self($peer, $logfile, $isWeb);
		}
		
		public function __construct(CachePeer $peer, $logfile, $isWeb = true)
		{
			$this->peer		= $peer;
			$this->isWeb	= $isWeb;
			$this->logger	=
				StreamLogger::create()->
				setOutputStream(FileOutputStream::create($logfile, true));
		}
		
		public function setBlackListActions($actions)
		{
			if (!empty($this->whiteListActions))
				throw new WrongStateException('You already setup black list!');
			
			$this->blackListActions = $actions;
			
			$this->actionFilter = true;
			
			return $this;
		}
		
		public function dropBlackListActions()
		{
			$this->blackListActions = array();
			
			$this->actionFilter = false;
			
			return $this;
		}
		
		public function setWhiteListActions($actions)
		{
			if (!empty($this->blackListActions))
				throw new WrongStateException('You already setup white list!');
			
			$this->whiteListActions = $actions;
			
			$this->actionFilter = true;
			
			return  $this;
		}
		
		public function dropWhiteListActions()
		{
			$this->whiteListActions = array();
			
			$this->actionFilter = false;
			
			return $this;
		}
		
		/**
		 * @return CachePeer
		**/
		public function mark($className)
		{
			return $this;
		}
		
		public function increment($key, $value)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->increment($key, $value);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('increment', $beginTime, $endTime, $key);
			
			return $value;
		}
		
		public function decrement($key, $value)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->decrement($key, $value);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('decrement', $beginTime, $endTime, $key);
			
			return $value;
		}
		
		public function getList($indexes)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->getList($indexes);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('getList', $beginTime, $endTime);
			
			return $value;
		}
		
		public function get($key)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->get($key);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('get', $beginTime, $endTime, $key);
			
			return $value;
		}
		
		public function delete($key)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->delete($key);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('delete', $beginTime, $endTime, $key);
			
			return $value;
		}
		
		/**
		 * @return CachePeer
		**/
		public function clean()
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->clean();
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('clean', $beginTime, $endTime);
			
			return $value;
		}
		
		public function isAlive()
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->isAlive();
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('isAlive', $beginTime, $endTime);
			
			return $value;
		}
		
		public function append($key, $data)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->append($key, $data);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('append', $beginTime, $endTime, $key);
			
			return $value;
		}
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->store($action, $key, $value, $expires);
			$endTime 	= $beginTime - microtime(true);
			
			$this->log('store + '.$action, $beginTime, $endTime, $key);
			
			return $value;
		}
		
		private function log($action, $beginTime, $endTime, $key = null)
		{
			if ($this->actionFilter) {
				if (
					!empty($this->blackListActions)
					&& in_array($action, $this->blackListActions)
				)
					return $this;
				
				if (
					!empty($this->whiteListActions)
					&& !in_array($action, $this->whiteListActions)
				)
					return $this;
			}
			
			$record = null;
			
			if ($this->isWeb)
				$record .= $_SERVER['REQUEST_URI']."\t";
			
			$record .= $action."\t".$key."\t".$beginTime."\t".$endTime;
			
			$this->logger->info($record);
			
			return $this;
		}
	}
?>