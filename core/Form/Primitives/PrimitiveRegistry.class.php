<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey V. Gorbylev                             *
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
	class PrimitiveRegistry extends IdentifiablePrimitive implements ListedPrimitive
	{
		// scalar id
		protected $scalar = true;

		public function setScalar($orly = false)
		{
			throw new WrongStateException();
		}

		public function getList()
		{
			if ($this->value)
				return ClassUtils::callStaticMethod(get_class($this->value).'::getList');
			elseif ($this->default)
				return ClassUtils::callStaticMethod(get_class($this->default).'::getList');
			else {
				$object = new $this->className(
					ClassUtils::callStaticMethod($this->className.'::getAnyId')
				);

				return $object->getObjectList();
			}

			Assert::isUnreachable();
		}

		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveRegistry
		 **/
		public function of($class)
		{
			$className = $this->guessClassName($class);

			Assert::classExists($className);

			Assert::isInstance($className, 'Registry');

			$this->className = $className;

			return $this;
		}

		public function importValue(/* Identifiable */ $value)
		{
			if ($value)
				Assert::isEqual(get_class($value), $this->className);
			else
				return parent::importValue(null);

			return $this->import(array($this->getName() => $value->getId()));
		}

		public function import($scope)
		{
			$result = parent::import($scope);

			if ($result === true) {
				try {
					$this->value = $this->makeRegistryById($this->value);
				} catch (MissingElementException $e) {
					$this->value = null;

					return false;
				}

				return true;
			}

			return $result;
		}

		/**
		 * @param $list
		 * @throws UnsupportedMethodException
		 */
		public function setList($list)
		{
			throw new UnsupportedMethodException('you cannot set list here, it is impossible, because list getted from registry classes');
		}

		/**
		 * @return null|string
		 */
		public function getChoiceValue()
		{
			if(
				($value = $this->getValue() ) &&
				$value instanceof Registry
			)
				return $value->getName();

			return null;
		}


		/**
		 * @return Registry|mixed|null
		 */
		public function getActualChoiceValue()
		{
			if(
				!$this->getChoiceValue() &&
				$this->getDefault()
			)
				return $this->getDefault()->getName();

			return null;
		}

		/**
		 * @param $id
		 * @return Registry|mixed
		 */
		protected function makeRegistryById($id)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveRegistry '{$this->name}'"
				);

			return new $this->className($id);
		}
	}