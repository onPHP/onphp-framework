<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Criteria
	**/
	final class PropertyPath
	{
		private $root		= null;
		private $path		= null;
		
		private $properties	= array();
		
		private static $daos	= array();
		private static $protos	= array(); // zergs suck anyway ;-)
		
		public function __construct($root, $path)
		{
			Assert::isString($path, 'non-string path given');
			
			if (is_object($root))
				$className = get_class($root);
			else {
				Assert::classExists($root);
				
				$className = $root;
			}
			
			$this->root = $className;
			$this->path = $path;
			
			$this->fetchHelpers($className);
			
			$proto = self::$protos[$className];
			
			$path = explode('.', $path);
			
			for ($i = 0, $size = count($path); $i < $size; ++$i) {
				$this->properties[$i]
					= $property
					= $proto->getPropertyByName($path[$i]);
				
				if ($className = $property->getClassName()) {
					$this->fetchHelpers($className);
					$proto = self::$protos[$className];
				} elseif ($i < $size) {
					continue;
				} else {
					throw new WrongArgumentException('corrupted path');
				}
			}
		}
		
		public function getPath()
		{
			return $this->path;
		}
		
		public function getRoot()
		{
			return $this->root;
		}
		
		/**
		 * @return AbstractProtoClass
		**/
		public function getFinalProto()
		{
			return self::$protos[$this->getFinalProperty()->getClassName()];
		}
		
		/**
		 * @return ProtoDAO
		**/
		public function getFinalDao()
		{
			return self::$daos[$this->getFinalProperty()->getClassName()];
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function getFinalProperty()
		{
			return end($this->properties);
		}
		
		/* void */ private function fetchHelpers($className)
		{
			if (isset(self::$protos[$className], self::$daos[$className]))
				return /* boo */;
			
			self::$protos[$className] = call_user_func(array($className, 'proto'));
			self::$daos[$className] = call_user_func(array($className, 'dao'));
			
			Assert::isTrue(
				(self::$protos[$className] instanceof AbstractProtoClass)
				&& (self::$daos[$className] instanceof ProtoDAO)
			);
		}
	}
?>