<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Simple wrapper to pinba php extention
	 * @see http://pinba.org/
	 */
	final class PinbaClient extends Singleton
	{
		private static $enabled = null;
		private $timers = array();
		private $queue = array();
		private $treeLogEnabled = false;
		
		
		/**
		 * @return PinbaClient
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public static function isEnabled()
		{
			if (self::$enabled === null)
				self::$enabled = ini_get("pinba.enabled") === "1";
			
			return self::$enabled;
		}
		
		public function setTreeLogEnabled($orly = true)
		{
			$this->treeLogEnabled = ($orly === true);
			
			return $this;
		}
		
		public function isTreeLogEnabled()
		{
			return $this->treeLogEnabled;
		}
		
		public function getTreeQueue()
		{
			return $this->queue;
		}
		
		public function timerStart($name, array $tags, array $data = array())
		{
			if (array_key_exists($name, $this->timers))
				throw new WrongArgumentException('a timer with the same name allready exists');
			
			if ($this->isTreeLogEnabled()) {
				
				$id = uniqid();
				$tags['treeId'] = $id;
				
				if (!empty($this->queue))
					$tags['treeParentId'] = end($this->queue);
				else
					$tags['treeParentId'] = 'root';
				
				$this->queue[] = $id;
			}
			
			$this->timers[$name] =
				count($data)
					? pinba_timer_start($tags, $data)
					: pinba_timer_start($tags);
			
			return $this;
		}
		
		public function timerStop($name)
		{
			 if ($this->isTreeLogEnabled())
				array_pop($this->queue);
			 
			 if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			 
			  pinba_timer_stop($this->timers[$name]);
			  
			  unset($this->timers[$name]);
			  
			  return $this;
		}
		
		public function isTimerExists($name)
		{
			return array_key_exists($name, $this->timers);
		}
		
		public function timerDelete($name)
		{
			 if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			
			pinba_timer_delete($this->timers[$name]);
			
			unset($this->timers[$name]);
			
			return $this;
		}
		
		public function timerGetInfo($name)
		{
			if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			
			return pinba_timer_get_info($this->timers[$name]);
		}
		
		public function setScriptName($name)
		{
			pinba_script_name_set($name);
			
			return $this;
		}
		
		public function setHostName($name)
		{
			pinba_hostname_set($name);
			
			return $this;
		}
		
		/**
		 * NOTE: You don't need to flush data manually. Pinba do it for you.
		 */
		public function flush()
		{
			pinba_flush();
		}
	}
?>