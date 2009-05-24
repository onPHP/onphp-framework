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
	 * Prototyped varian of DAO synchronizer.
	**/
	final class DaoSynchronizer extends CustomizableDaoSynchronizer
	{
		public static function create()
		{
			return new self;
		}

		public function setMaster(GenericDAO $master)
		{
			Assert::isInstance($master, 'ProtoDAO');

			return parent::setMaster($master);
		}

		public function setSlave(GenericDAO $slave)
		{
			Assert::isInstance($slave, 'ProtoDAO');

			return parent::setSlave($slave);
		}

		protected function sync($old, $object)
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

				return parent::sync($old, $object);
			}

			return 0;
		}

		protected function delete($slaveObject)
		{
			// FIXME: remove presentation logic
			echo ($this->reallyDelete ? 'really ' : null)
				."deleted: ".$slaveObject.PHP_EOL;

			return parent::delete($slaveObject);
		}

		protected function insert($masterObject)
		{
			// FIXME: remove presentation logic
			echo "inserted: ".$masterObject.PHP_EOL;

			return parent::insert($masterObject);
		}

		private function changed($old, $object, $properties)
		{
			// FIXME: remove presentation logic
			echo "updated: ".$old.' ';

			foreach ($properties as $propertyName => $property) {
				$getter = $property->getGetter();

				$oldValue = $old->$getter();
				$newValue = $object->$getter();

				echo "[$propertyName: '$oldValue' => '$newValue'] ";
			}

			// FIXME: remove presentation logic
			echo PHP_EOL;

			return $this;
		}
	}
?>