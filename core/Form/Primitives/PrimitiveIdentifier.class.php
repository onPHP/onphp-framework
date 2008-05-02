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
	 * @ingroup Primitives
	**/
	class PrimitiveIdentifier extends IdentifiablePrimitive
	{
		private $info = null;
		
		private $methodName	= 'getById';
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveIdentifier
		**/
		public function of($class)
		{
			$className = $this->guessClassName($class);
			
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$this->info = new ReflectionClass($className);
			
			Assert::isTrue(
				$this->info->implementsInterface('DAOConnected'),
				"class '{$className}' must implement DAOConnected interface"
			);
			
			$this->className = $className;
			
			return $this;
		}
		
		/**
		 * @return GenericDAO
		**/
		public function dao()
		{
			Assert::isNotNull(
				$this->className,
				'specify class name first of all'
			);
			
			return call_user_func(array($this->className, 'dao'));
		}
		
		/**
		 * @return PrimitiveIdentifier
		**/
		public function setMethodName($methodName)
		{
			if (strpos($methodName, '::') === false) {
				$dao = $this->dao();
				
				Assert::isTrue(
					method_exists($dao, $methodName),
					"knows nothing about '".get_class($dao)
					."::{$methodName}' method"
				);
			} else
				ClassUtils::checkStaticMethod($methodName);
			
			$this->methodName = $methodName;
			
			return $this;
		}
		
		public function importValue($value)
		{
			try {
				if ($value instanceof Identifiable) {
					Assert::isTrue(
						ClassUtils::isInstanceOf($value, $this->className)
					);
					
					return
						$this->import(
							array($this->getName() => $value->getId())
						);
				} elseif ($value) {
					Assert::isPositiveInteger($value);
					
					return $this->import(array($this->getName() => $value));
				}
			} catch (WrongArgumentException $e) {
				return false;
			}
			
			return parent::importValue(null);
		}
		
		public function import(array $scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifier '{$this->name}'"
				);
			
			$className = $this->className;
			
			if (
				isset($scope[$this->name])
				&& $scope[$this->name] instanceof $className
			) {
				$value = $scope[$this->name];
				
				$this->raw = $value->getId();
				$this->setValue($value);
				
				return $this->imported = true;
			}
			
			$result = parent::import($scope);
			
			if ($result === true) {
				try {
					$result =
						(strpos($this->methodName, '::') === false)
							? $this->dao()->{$this->methodName}($this->value)
							: ClassUtils::callStaticMethod(
								$this->methodName, $this->value
							);
					
					if (!$result || !($result instanceof $className)) {
						$this->value = null;
						return false;
					}
					
					$this->value = $result;
					
				} catch (ObjectNotFoundException $e) {
					$this->value = null;
					return false;
				}
				
				return true;
			}
			
			return $result;
		}
	}
?>