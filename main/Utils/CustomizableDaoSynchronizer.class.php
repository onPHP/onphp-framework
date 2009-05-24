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

	class CustomizableDaoSynchronizer
	{
		protected $dryRun = false;
		protected $reallyDelete = false;

		protected $master = null;
		protected $slave = null;

		private $masterProjection = null;
		private $slaveProjection = null;

		private $masterKeyProperty = 'id';
		private $slaveKeyProperty = 'id';

		public static function create()
		{
			return new self;
		}

		public function setDryRun($dryRun)
		{
			$this->dryRun = $dryRun;

			return $this;
		}

		// FIXME: isDryRun
		public function getDryRun()
		{
			return $this->dryRun;
		}

		public function setReallyDelete($reallyDelete)
		{
			$this->reallyDelete = $reallyDelete;

			return $this;
		}

		// FIXME: isReallyDelete
		public function getReallyDelete()
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
			// FIXME: remove presentation logic
			echo 'Synchronizing: '.get_class($this->master)
				.' => '.get_class($this->slave).PHP_EOL;

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

			$deleted = $inserted = $updated = 0;

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
					$updated += $this->sync($slaveObject, $masterObject);

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
					$deleted++;

					$this->delete($slaveObject);

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
					$inserted++;

					$this->insert($masterObject);

					$masterIterator->next();

				} else {
					Assert::isUnreachable('how did you get here?');
				}
			}

			// FIXME: remove presentation logic
			echo "Total: updated: $updated, deleted: $deleted, ".
				"inserted: $inserted".PHP_EOL.PHP_EOL;

			return $this;
		}

		protected function sync($old, $object)
		{
			if (!$this->dryRun)
				$this->slave->merge($object);

			return 1;
		}

		protected function delete($slaveObject)
		{
			// FIXME: assertion for method's existence is missing
			$slaveGetter = 'get'.ucfirst($this->slaveKeyProperty);

			if (!$this->dryRun && $this->reallyDelete)
				$this->slave->dropById($slaveObject->$slaveGetter());

			return 1;
		}

		protected function insert($masterObject)
		{
			if (!$this->dryRun)
				$this->slave->import($masterObject);

			return 1;
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