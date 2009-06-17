<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	class CustomizableDaoSynchronizer
	{
		protected $dryRun			= false;
		protected $reallyDelete		= false;
		
		protected $master			= null;
		protected $slave			= null;
		
		private $masterProjection	= null;
		private $slaveProjection	= null;
		
		private $masterKeyProperty	= 'id';
		private $slaveKeyProperty	= 'id';
		
		private $totalUpdated		= 0;
		private $totalInserted		= 0;
		private $totalDeleted		= 0;
		
		public static function create()
		{
			return new self;
		}
		
		public function setDryRun($dryRun)
		{
			$this->dryRun = $dryRun;
			
			return $this;
		}
		
		public function isDryRun()
		{
			return $this->dryRun;
		}
		
		public function setReallyDelete($reallyDelete)
		{
			$this->reallyDelete = $reallyDelete;
			
			return $this;
		}
		
		public function isReallyDelete()
		{
			return $this->reallyDelete;
		}
		
		public function setMaster(GenericDAO $master)
		{
			$this->master = $master;
			
			return $this;
		}
		
		/**
		 * @return GenericDAO
		**/
		public function getMaster()
		{
			return $this->master;
		}
		
		public function setSlave(GenericDAO $slave)
		{
			$this->slave = $slave;
			
			return $this;
		}
		
		/**
		 * @return GenericDAO
		**/
		public function getSlave()
		{
			return $this->slave;
		}
		
		public function setMasterKeyProperty($masterKeyProperty)
		{
			$this->masterKeyProperty = $masterKeyProperty;
			
			return $this;
		}
		
		public function getMasterKeyProperty()
		{
			return $this->masterKeyProperty;
		}
		
		public function setSlaveKeyProperty($slaveKeyProperty)
		{
			$this->slaveKeyProperty = $slaveKeyProperty;
			
			return $this;
		}
		
		public function getSlaveKeyProperty()
		{
			return $this->slaveKeyProperty;
		}
		
		public function setMasterProjection(ObjectProjection $masterProjection)
		{
			$this->masterProjection = $masterProjection;
			
			return $this;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function getMasterProjection()
		{
			return $this->masterProjection;
		}
		
		public function setSlaveProjection(ObjectProjection $slaveProjection)
		{
			$this->slaveProjection = $slaveProjection;
			
			return $this;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function getSlaveProjection()
		{
			return $this->slaveProjection;
		}
		
		public function run()
		{
			$masterIterator = new DaoIterator();
			$slaveIterator = new DaoIterator();
			
			$masterIterator->setDao($this->master);
			$slaveIterator->setDao($this->slave);
			
			if ($this->masterKeyProperty)
				$masterIterator->setKeyProperty($this->masterKeyProperty);
			
			if ($this->slaveKeyProperty)
				$slaveIterator->setKeyProperty($this->slaveKeyProperty);
			
			if ($this->masterProjection)
				$masterIterator->setProjection($this->masterProjection);
			
			if ($this->slaveProjection)
				$slaveIterator->setProjection($this->slaveProjection);
			
			$this->totalDeleted = 0;
			$this->totalInserted = 0;
			$this->totalUpdated = 0;
			
			while ($masterIterator->valid() || $slaveIterator->valid()) {
				$masterObject = $masterIterator->current();
				$slaveObject = $slaveIterator->current();
				
				$masterObject = $this->convertMasterObjectToSlave($masterObject);
				
				$masterGetter = 'get'.ucfirst($this->masterKeyProperty);
				$slaveGetter = 'get'.ucfirst($this->slaveKeyProperty);
				
				if (
					$masterObject && $slaveObject
					&& (
						$masterObject->$masterGetter()
							== $slaveObject->$slaveGetter()
					)
				) {
					if ($this->sync($slaveObject, $masterObject))
						$this->totalUpdated++;
					
					$masterIterator->next();
					$slaveIterator->next();
				} elseif (
					!$masterObject
					|| (
						$slaveObject
						&& $this->compareKeys(
							$masterObject->$masterGetter(),
							$slaveObject->$slaveGetter()
						) > 0
					)
				) {
					if ($this->delete($slaveObject))
						$this->totalDeleted++;
					
					$slaveIterator->next();
				} elseif (
					!$slaveObject
					|| (
						$masterObject
						&& $this->compareKeys(
							$masterObject->$masterGetter(),
							$slaveObject->$slaveGetter()
						) < 0
					)
				) {
					if ($this->insert($masterObject))
						$this->totalInserted++;
					
					$masterIterator->next();
					
				} else {
					Assert::isUnreachable('how did you get here?');
				}
			}
			
			return $this;
		}
		
		public function getTotalInserted()
		{
			return $this->totalInserted;
		}
		
		public function getTotalDeleted()
		{
			return $this->totalDeleted;
		}
		
		public function getTotalUpdated()
		{
			return $this->totalUpdated;
		}
		
		protected function sync($old, $object)
		{
			if (!$this->dryRun)
				$this->slave->merge($object);
			
			return true;
		}
		
		protected function delete($slaveObject)
		{
			$slaveGetter = 'get'.ucfirst($this->slaveKeyProperty);
			
			Assert::methodExists($slaveObject, $slaveGetter);
			
			if (!$this->dryRun && $this->reallyDelete)
				$this->slave->dropById($slaveObject->$slaveGetter());
			
			return true;
		}
		
		protected function insert($masterObject)
		{
			if (!$this->dryRun)
				$this->slave->import($masterObject);
			
			return true;
		}
		
		protected function compareKeys($min, $sub)
		{
			if ($min > $sub) 
				return 1;
			elseif ($min < $sub)
				return -1;
			
			return 0;
		}
		
		private function convertMasterObjectToSlave($masterObject)
		{
			if (
				$masterObject
				&& (
					call_user_func(
						array($this->slave->getObjectName(), 'create')
					)
					instanceof SynchronizableObject
				)
			)
				$masterObject = call_user_func(
					array(
						$this->slave->getObjectName(),
						'createFromMasterObject'
					),
					$masterObject
				);
			
			return $masterObject;
		}
	}
?>