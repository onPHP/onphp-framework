<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
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
		/* void */ public static function getValuesFrom(
			Identifiable $object, Form $form
		)
		{
			$class = new ReflectionClass($object);
			$primitives = $form->getPrimitiveList();
			
			foreach ($class->getProperties() as $property) {
				$name = $property->getName();
				
				if (isset($primitives[$name])) {
					
					$getter = 'get'.ucfirst($name);
					$prm = $primitives[$name];
					
					// hasMethod() is 5.1 only
					try {
						if (
							$class->getMethod($getter)
							&& ($value = $object->$getter()) !== null
						) {
							$fake =
								array(
									$name =>
										$value instanceof Identifiable
											? $value->getId()
											: $value
								);
							
							$form->importOne($name, $fake);
						}
					} catch (ReflectionException $e) {
						// no such method
					}
				}
			}
		}
		
		/* void */ public static function setPropertiesTo(
			Identifiable $object, Form $form
		)
		{
			$class = new ReflectionClass($object);
			
			foreach ($form->getPrimitiveList() as $name => $prm) {
				$setter = 'set'.ucfirst($name);
				
				// hasMethod() is 5.1 only
				try {
					if (
						$class->getMethod($setter)
						&& ($value = $prm->getValue()) !== null
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
						
						$object->$setter($value);
					}
				} catch (ReflectionException $e) {
					// no such method
				}
			}
		}
	}
?>