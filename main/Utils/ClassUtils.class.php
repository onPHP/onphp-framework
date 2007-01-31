<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry E. Demidov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class ClassUtils extends StaticFactory
	{
		public static function copyProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				
				$name = $property->getName();
				$getter = 'get'.ucfirst($name);
				$setter = 'set'.ucfirst($name);
				
				if (
					($class->hasMethod($getter))
					&& ($class->hasMethod($setter))
				) {
					
					$sourceValue = $source->$getter();
					
					if ($sourceValue === null) {
						
						$setMethood = $class->getMethod($setter);
						$parameterList = $setMethood->getParameters();
						$firstParameter = $parameterList[0];
						
						if ($firstParameter->allowsNull())
							$destination->$setter($sourceValue);
						
					} else {
						$destination->$setter($sourceValue);
					}
				}
			}
			
			return $destination;
		}
		
		public static function copyNotNullProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				
				$name = $property->getName();				
				$getter = 'get'.ucfirst($name);
				$setter = 'set'.ucfirst($name);
				
				if (
					($class->hasMethod($getter))
					&& ($class->hasMethod($setter))
				) {
					$value = $source->$getter();
					if ($value !== null)
						$destination->$setter($value);
				}
			}
			
			return $destination;
		}
		
		public static function fillNullProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				
				$name = $property->getName();				
				$getter = 'get'.ucfirst($name);
				$setter = 'set'.ucfirst($name);
				
				if (
					($class->hasMethod($getter))
					&& ($class->hasMethod($setter))
				) {
					
					$destinationValue = $destination->$getter();
					$sourceValue = $source->$getter();
					
					if (
						($destinationValue === null)
						&& ($sourceValue !== null) 
					) {
						$destination->$setter($sourceValue);
					}
				}
			}
			
			return $destination;
		}
	}
?>