<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Form
	**/
	final class FormUtils extends StaticFactory
	{
		/* void */ public static function object2form(
			$object, Form $form, $ignoreNull = true
		)
		{
			Assert::isTrue(is_object($object));
			
			$primitives = $form->getPrimitiveList();
			$class = new ReflectionClass($object);
			
			$isPrototyped = ($object instanceof Prototyped);
			
			if ($isPrototyped) {
				$propertyList = $object->proto()->getPropertyList();
			} else {
				$propertyList = $class->getProperties();
			}
			
			foreach ($propertyList as $property) {
				$name = $property->getName();
				
				if (isset($primitives[$name])) {
					$getter = 'get'.ucfirst($name);
					if ($class->hasMethod($getter)) {
						$value = $object->$getter();
						if (!$ignoreNull || ($value !== null)) {
							$form->importValue($name, $value);
						}
					}
				}
			}
		}
		
		/* void */ public static function form2object(
			Form $form, $object, $ignoreNull = true
		)
		{
			Assert::isTrue(is_object($object));
			
			$class = new ReflectionClass($object);
			
			foreach ($form->getPrimitiveList() as $name => $prm) {
				$setter = 'set'.ucfirst($name);
				
				if ($prm instanceof ListedPrimitive)
					$value = $prm->getChoiceValue();
				else
					$value = $prm->getValue();
				
				if (
					$class->hasMethod($setter)
					&& (!$ignoreNull || ($value !== null))
				) {
					if ( // magic!
						$prm->getName() == 'id'
						&& (
							$value instanceof Identifiable
						)
					) {
						$value = $value->getId();
					}
					
					if ($value === null) {
						$dropper = 'drop'.ucfirst($name);
						
						if ($class->hasMethod($dropper)) {
							$object->$dropper();
							continue;
						}
					}
					
					$object->$setter($value);
				}
			}
		}
		
		public static function checkPrototyped(Prototyped $object)
		{
			$form = $object->proto()->makeForm();
			
			self::object2form($object, $form, false);
			
			return $form->getErrors();
		}
	}
?>