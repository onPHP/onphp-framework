<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

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
			
			if ($object instanceof Prototyped) {
				$proto = $object->proto();
				
				foreach (array_keys($proto->getExpandedPropertyList()) as $name) {
					if ($form->primitiveExists($name)) {
						$proto->importPrimitive(
							$name,
							$form,
							$form->get($name),
							$object,
							$ignoreNull
						);
					}
				}
			} else {
				$class = new ReflectionClass($object);
				
				foreach ($class->getProperties() as $property) {
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
		}
		
		/* void */ public static function form2object(
			Form $form, $object, $ignoreNull = true
		)
		{
			Assert::isTrue(is_object($object));
			
			if ($object instanceof Prototyped) {
				$proto = $object->proto();
				$list = $proto->getExpandedPropertyList();
				
				foreach ($form->getPrimitiveList() as $name => $prm) {
					if (isset($list[$name])) {
						$proto->exportPrimitive($name, $prm, $object, $ignoreNull);
					}
				}
			} else {
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
		}
		
		public static function checkPrototyped(Prototyped $object)
		{
			$form = $object->proto()->makeForm();
			
			self::object2form($object, $form, false);
			
			return $form->getErrors();
		}
	}
?>