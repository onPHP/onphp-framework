<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Form
	**/
	final class FormUtils extends StaticFactory
	{
		/* void */ public static function object2form(
			Identifiable $object, Form $form, $ignoreNull = true
		)
		{
			$class = new ReflectionClass($object);
			$primitives = $form->getPrimitiveList();
			
			foreach ($class->getProperties() as $property) {
				$name = $property->getName();
				
				if (isset($primitives[$name])) {
					
					$getter	= 'get'.ucfirst($name);
					$value	= $object->$getter();
					$prm	= $primitives[$name];
					
					try {
						if (
							$class->hasMethod($getter)
							&& ($ignoreNull && ($value !== null)
						) {
							// PrimitiveIdentifier, Enumerations
							if ($value instanceof Identifiable)
								$fake = array($name => $value->getId());
							// PrimitiveDate
							elseif ($value instanceof Stringable)
								$fake = array($name => $value->toString());
							// PrimitiveBoolean
							elseif (is_bool($value) && $value === false) 
								$fake = array();
							// everything else
							else
								$fake = array($name => $value);
							
							if ($prm instanceof ComplexPrimitive) {
								if ($prm->importSingle($fake) === true)
									$form->markGood($name);
							} else
								$form->importOne($name, $fake);
						}
					} catch (ReflectionException $e) {
						// no such method
					}
				}
			}
		}
		
		/* void */ public static function form2object(
			Form $form, Identifiable $object, $ignoreNull = true
		)
		{
			$class = new ReflectionClass($object);
			
			foreach ($form->getPrimitiveList() as $name => $prm) {
				$setter = 'set'.ucfirst($name);
				$value = $prm->getValue();
				
				try {
					if (
						$class->hasMethod($setter)
						&& ($ignoreNull && ($value !== null))
					) {
						if ($prm instanceof PrimitiveList) {
							$list = $prm->getList();
							$value = $list[$value];
						} elseif ( // magic!
							$prm->getName() == 'id'
							&& $value instanceof Identifiable
						) {
							$value = $value->getId();
						}
						
						if ($value === null) {
							$dropper = 'drop'.ucfirst($name);
							
							if ($class->hasMethod($dropper))
								$object->$dropper();
						}

						$object->$setter($value);
					}
				} catch (ReflectionException $e) {
					// no such method
				}
			}
		}
	}
?>