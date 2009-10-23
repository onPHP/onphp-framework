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
		public static function create(
			CachePeer $peer, $logfile, $isWeb = true, $appendFile = true
		)
		{
			return new self($peer, $logfile, $isWeb, $appendFile);
		}
		
		public function __construct(
			CachePeer $peer, $logfile, $isWeb = true, $appendFile = true
		)
		{
			$this->peer		= $peer;
			$this->isWeb	= $isWeb;
			$this->logger	=
				StreamLogger::create()->
				setOutputStream(FileOutputStream::create($logfile, $appendFile));
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
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('increment', $totalTime, $key);
			
			return $value;
		}
		
		public function decrement($key, $value)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->decrement($key, $value);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('decrement', $totalTime, $key);
			
			return $value;
		}
		
		public function getList($indexes)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->getList($indexes);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('getList', $totalTime);
			
			return $value;
		}
		
		public function get($key)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->get($key);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('get', $totalTime, $key);
			
			return $value;
		}
		
		public function delete($key)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->delete($key);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('delete', $totalTime, $key);
			
			return $value;
		}
		
		/**
		 * @return CachePeer
		**/
		public function clean()
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->clean();
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('clean', $totalTime);
			
			return $value;
		}
		
		public function isAlive()
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->isAlive();
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('isAlive', $totalTime);
			
			return $value;
		}
		
		public function append($key, $data)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->append($key, $data);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('append', $totalTime, $key);
			
			return $value;
		}
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$beginTime 	= microtime(true);
			$value 		= $this->peer->store($action, $key, $value, $expires);
			$totalTime 	=  microtime(true) - $beginTime;
			
			$this->log('store + '.$action, $totalTime, $key);
			
			return $value;
		}
		
		private function log($action, $totalTime, $key = null)
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
				$record .= (
					(isset($_SERVER['SSI_REQUEST_URI']))
						? $_SERVER['SSI_REQUEST_URI']
						: $_SERVER['REQUEST_URI']
				)."\t";
			
			$record .= $action."\t".$key."\t".$totalTime;
			
			$this->logger->info($record);
			
			return $this;
		}
	}
?>