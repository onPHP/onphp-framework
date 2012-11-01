<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	class PrimitiveIdentifier extends IdentifiablePrimitive
	{
		private $methodName	= 'getById';
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\PrimitiveIdentifier
		**/
		public function of($class)
		{
			$className = $this->guessClassName($class);
			
			Assert::classExists($className);
			
			Assert::isInstance(
				$className,
				'\Onphp\DAOConnected',
				"class '{$className}' must implement DAOConnected interface"
			);
			
			$this->className = $className;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\GenericDAO
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
		 * @return \Onphp\PrimitiveIdentifier
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
			if ($value instanceof Identifiable) {
				try {
					Assert::isInstance($value, $this->className);
					
					return
						$this->import(
							array($this->getName() => $value->getId())
						);
				
				} catch (WrongArgumentException $e) {
					return false;
				}
			}
			
			return parent::importValue($value);
		}
		
		public function import($scope)
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
					$result = $this->actualImportValue($this->value);
					
					Assert::isInstance($result, $className);
					
					$this->value = $result;
					
					return true;
				
				} catch (WrongArgumentException $e) {
					// not imported
				} catch (ObjectNotFoundException $e) {
					// not imported
				}
				
				$this->value = null;
				
				return false;
			}
			
			return $result;
		}
		
		protected function actualImportValue($value)
		{
			return
				(strpos($this->methodName, '::') === false)
					? $this->dao()->{$this->methodName}($value)
					: ClassUtils::callStaticMethod(
						$this->methodName, $value
					);
		}
	}
?>