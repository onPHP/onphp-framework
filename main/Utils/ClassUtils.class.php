<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class ClassUtils extends StaticFactory
	{
		const CLASS_NAME_PATTERN = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
		
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
		
		public static function isClassName($className)
		{
			return preg_match('/^'.self::CLASS_NAME_PATTERN.'$/', $className);
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
				$class = new ReflectionClass($className);
			} catch (ReflectionException $e) {
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
		
		/* void */ public static function dtoObject2xml(
			$object,
			$classMap,
			/* DomElement */ $xmlDoc = null,
			$ignoreNull = false
		)
		{
			Assert::isTrue(is_object($object));
			
			$class = new ReflectionClass($object);
			
			if (array_key_exists($class->getName(), $classMap))
				$className = $classMap[$class->getName()];
			else
				$className = $class->getName();
			
			$root = new DomElement($className);
			
			if ($xmlDoc)
				$xmlDoc->appendChild($root);
			
			$propertyList = $class->getProperties();
			
			foreach ($propertyList as $property) {
				
				$name = $property->getName();
				
				$getter = 'get'.ucfirst($name);
				
				if ($class->hasMethod($getter)) {
					$value = $object->$getter();
						if (!$ignoreNull) {
							if (is_array($value)) {
								$element = new DomElement($name);
								
								$root->appendChild($element);
								
								foreach ($value as $innerObject) {
									ClassUtils::dtoObject2xml(
										$innerObject,
										$classMap,
										$element
									);
								}
							} elseif (is_object($value)) {
								if ($value instanceof Timestamp) {
									$element =
										new DomElement(
											$name,
											$value->toISOString()
										);
								
									$root->appendChild($element);
								} else {
									throw new WrongArgumentException(
										'I dont know how to convert object of '
										. get_class($value) . ' class to xml.'
									);
								}
							} else {
								$element = new DomElement($name, $value);
								
								$root->appendChild($element);
							}
						}
				}
			}
		}
		
		/* void */ public static function xml2dtoObject(
			&$object,
			$classMap,
			$simpleXml,
			$ignoreNull = false
		)
		{
			$className = $simpleXml->getName();
			
			if (array_key_exists($className, $classMap))
				$className = $classMap[$className];
			
			if (!$object)
				$object = new $className;
			
			$class = new ReflectionClass($object);
			
			$propertyList = $class->getProperties();
			
			foreach ($propertyList as $property) {
				
				$name = $property->getName();
				
				$setter = 'set'.ucfirst($name);
				
				if ($class->hasMethod($setter)) {
					if (!$ignoreNull) {
						if ($children = $simpleXml->$name->children()) {
							$innerObjects = array();
							
							foreach ($children as $child) {
								ClassUtils::xml2dtoObject(
									$innerObject,
									$classMap,
									$child
								);
								
								$innerObjects[] = $innerObject;
								unset($innerObject);
							}
							
							$object->$setter($innerObjects);
						} else {
							$object->$setter((string) $simpleXml->$name);
						}
					}
				}
			}
		}
	}
?>