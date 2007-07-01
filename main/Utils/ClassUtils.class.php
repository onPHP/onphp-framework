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
		/* void */ public static function copyProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				$name = ucfirst($property->getName());
				$getter = 'get'.$name;
				$setter = 'set'.$name;
				
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
		}
		
		/* void */ public static function copyNotNullProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				$name = ucfirst($property->getName());
				$getter = 'get'.$name;
				$setter = 'set'.$name;
				
				if (
					($class->hasMethod($getter))
					&& ($class->hasMethod($setter))
				) {
					$value = $source->$getter();
					if ($value !== null)
						$destination->$setter($value);
				}
			}
		}
		
		/* void */ public static function fillNullProperties($source, $destination)
		{
			Assert::isTrue(get_class($source) == get_class($destination));
			
			$class = new ReflectionClass($source);
			
			foreach ($class->getProperties() as $property) {
				$name = ucfirst($property->getName());
				$getter = 'get'.$name;
				$setter = 'set'.$name;
				
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
		}
		
		public static function isInstanceOf($object, $class)
		{
			if (is_object($class)) {
				$className = get_class($class);
			} elseif (is_string($class)) {
				$className = $class;
			} else {
				throw new WrongArgumentException('strange class given');
			}
			
			if (is_string($object))
				$object = new $object;
			elseif (!is_object($object))
				throw new WrongArgumentException('strange object given');
			
			if (is_subclass_of($object, $className))
				return true;
			// works well in >=5.2, and harmless for previous versions
			elseif ($object instanceof $className)
				return true;
			
			$info = new ReflectionClass($className);
			
			return $info->isInstance($object);
		}
		
		public static function callStaticMethod($methodSignature/* ... */)
		{
			self::checkStaticMethod($methodSignature);
			
			$agruments = func_get_args();
			array_shift($agruments);
			
			return
				call_user_func_array(
					split('::', $methodSignature),
					$agruments
				);
		}
		
		/* void */ public static function checkStaticMethod($methodSignature)
		{
			$nameParts = explode('::', $methodSignature);
			
			if (count($nameParts) != 2)
				throw new WrongArgumentException('incorrect method signature');
			
			$className = $nameParts[0];
			$methodName = $nameParts[1];
			
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$class = new ReflectionClass($className);
			
			Assert::isTrue(
				$class->hasMethod($methodName),
				"knows nothing about '$className::{$methodName}' method"
			);
			
			$method = $class->getMethod($methodName);
			
			Assert::isTrue(
				$method->isStatic(),
				"method is not static '$className::{$methodName}'"
			);
		}
	}
?>