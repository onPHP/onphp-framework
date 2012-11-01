<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	namespace Onphp;

	final class ClassUtils extends StaticFactory
	{
		const CLASS_NAME_PATTERN = '[a-zA-Z_\x7f-\xff\\\\][a-zA-Z0-9_\x7f-\xff\\\\]*';
		
		/* void */ public static function copyProperties($source, $destination)
		{
			Assert::isEqual(get_class($source), get_class($destination));
			
			$class = new \ReflectionClass($source);
			
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
			
			$class = new \ReflectionClass($source);
			
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
			
			$class = new \ReflectionClass($source);
			
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
		
		public static function isClassName($className)
		{
			if (!is_string($className))
				return false;

			return preg_match('/^'.self::CLASS_NAME_PATTERN.'$/', $className) > 0;
		}
		
		/// to avoid dependency on SPL's class_implements
		public static function isClassImplements($what)
		{
			static $classImplements = null;
			
			if (!$classImplements) {
				if (!function_exists('class_implements')) {
					$classImplements = create_function(
						'$what',
						'
							$info = new ReflectionClass($what);
							return $info->getInterfaceNames();
						'
					);
				} else {
					$classImplements = 'class_implements';
				}
			}
			
			return $classImplements($what, true);
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
			
			if (
				is_string($object)
				&& self::isClassName($object)
			) {
				if ($object == $className)
					return true;
				elseif (is_subclass_of($object, $className))
					return true;
				else
					return in_array(
						$class,
						self::isClassImplements($object)
					);
			} elseif (is_object($object)) {
				return $object instanceof $className;
				
			} else {
				throw new WrongArgumentException('strange object given');
			}
		}
		
		public static function callStaticMethod($methodSignature /* , ... */)
		{
			$agruments = func_get_args();
			array_shift($agruments);
			
			return
				call_user_func_array(
					self::checkStaticMethod($methodSignature),
					$agruments
				);
		}
		
		public static function checkStaticMethod($methodSignature)
		{
			$nameParts = explode('::', $methodSignature, 2);
			
			if (count($nameParts) != 2)
				throw new WrongArgumentException('incorrect method signature');
			
			list($className, $methodName) = $nameParts;
			
			try {
				$class = new \ReflectionClass($className);
			} catch (\ReflectionException $e) {
				throw new ClassNotFoundException($className);
			}
			
			Assert::isTrue(
				$class->hasMethod($methodName),
				"knows nothing about '{$className}::{$methodName}' method"
			);
			
			$method = $class->getMethod($methodName);
			
			Assert::isTrue(
				$method->isStatic(),
				"method is not static '{$className}::{$methodName}'"
			);
			
			Assert::isTrue(
				$method->isPublic(),
				"method is not public '{$className}::{$methodName}'"
			);
			
			return $nameParts;
		}
		
		/* void */ public static function preloadAllClasses()
		{
			foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
				foreach (
					glob(
						$directory.DIRECTORY_SEPARATOR.'/*'.EXT_CLASS,
						GLOB_NOSORT
					)
					as $file
				) {
					$className = basename($file, EXT_CLASS);
					
					if (
						!class_exists($className)
						&& !interface_exists($className)
						&& !(function_exists('trait_exists') && trait_exists($className))
					) {
						include $file;
					}
				}
			}
		}
	}
?>