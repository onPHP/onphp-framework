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
	 * Prototyped variant of DAO synchronizer.
	**/
	class DaoSynchronizer extends CustomizableDaoSynchronizer
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
				return $this->changed($old, $object, $changed);
			}
			
			return 0;
		}
		
		protected function changed($old, $object, $properties)
		{
			return parent::sync($old, $object);
		}
	}
?>
