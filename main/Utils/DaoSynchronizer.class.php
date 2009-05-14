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

	final class DaoSynchronizer
	{
		private $master = null;
		private $slave = null;

		private $masterProjection = null;
		private $slaveProjection = null;

		private $masterKeyProperty = 'id';
		private $slaveKeyProperty = 'id';

		private $dryRun = false;
		private $reallyDelete = false;


		public static function create()
		{
			return new self;
		}

		public function setDryRun($dryRun)
		{
			$this->dryRun = $dryRun;

			return $this;
		}

		public function getDryRun()
		{
			return $this->dryRun;
		}

		public function setReallyDelete($reallyDelete)
		{
			$this->reallyDelete = $reallyDelete;

			return $this;
		}

		public function getReallyDelete()
		{
			return $this->reallyDelete;
		}

		public function setMaster(ProtoDAO $master)
		{
			$this->master = $master;

			return $this;
		}

		/**
		 * @return ProtoDAO
		**/
		public function getMaster()
		{
			return $this->master;
		}

		public function setSlave(ProtoDAO $slave)
		{
			$this->slave = $slave;

			return $this;
		}

		/**
		 * @return ProtoDAO
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
						&& $masterObject->$masterGetter()
							> $slaveObject->$slaveGetter()
					)
				) {
					$deleted++;

					$this->delete($slaveObject);

					$slaveIterator->next();
				} elseif (
					!$slaveObject
					|| (
						$masterObject
						&& $masterObject->$masterGetter()
							< $slaveObject->$slaveGetter()
					)
				) {
					$inserted++;

					$this->insert($masterObject);

					$masterIterator->next();
				}
			}

			echo "Total: updated: $updated, deleted: $deleted,".
				"inserted: $inserted".PHP_EOL.PHP_EOL;

			return $this;
		}

		private function delete($slaveObject)
		{
			echo ($this->reallyDelete ? 'really ' : null)
				."deleted: ".$slaveObject.PHP_EOL;

			$slaveGetter = 'get'.ucfirst($this->slaveKeyProperty);

			if (!$this->dryRun && $this->reallyDelete)
				$this->slave->dropById($slaveObject->$slaveGetter());

			return 1;
		}

		private function insert($masterObject)
		{
			echo "inserted: ".$masterObject.PHP_EOL;

			if (!$this->dryRun)
				$this->slave->import($masterObject);

			return 1;
		}

		private function sync($old, $object)
		{
			$changed = array();

			foreach (
				$this->slave->getProtoClass()->
					getPropertyList() as $property
			) {
				$getter = $property->getGetter();

				if ($property->getClassName() === null) {
					if ($old->$getter() != $object->$getter())
						$changed[$property->getName()] = $property;

				} else {
					if (
						(
							is_object($old->$getter())
							&& !$old->$getter()->isEqualTo($object->$getter())
						)
						|| (!$old->$getter() && $object->$getter())
					)
						$changed[$property->getName()] = $property;
				}
			}

			if ($changed) {
				$this->changed($old, $object, $changed);

				return 1;
			}

			return 0;
		}

		private function changed($old, $object, $properties)
		{
			echo "updated: ".$old.' ';

			foreach ($properties as $propertyName => $property) {
				$getter = $property->getGetter();

				$oldValue = $old->$getter();
				$newValue = $object->$getter();

				echo "[$propertyName: '$oldValue' => '$newValue'] ";
			}

			echo PHP_EOL;

			if (!$this->dryRun)
				$this->slave->merge($object);
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
