<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov, Anton Lebedevich        *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class PlainForm
	{
		protected $aliases		= array();
		protected $primitives	= array();
		
		public function addAlias($primitiveName, $alias)
		{
			if (!isset($this->primitives[$primitiveName]))
				throw new ObjectNotFoundException("{$primitiveName} does not exist");

			$this->aliases[$alias] = $primitiveName;
			
			return $this;
		}
		
		public function primitiveExist($name)
		{
			return
				(
					isset($this->primitives[$name]) ||
					isset($this->aliases[$name])
				);
		}
		
		public function add(BasePrimitive $prm, $alias = null)
		{
			$name = &$prm->getName();
			
			if (isset($this->primitives[$name]))
				throw new DuplicateObjectException("i'm already exists!");

			$this->primitives[$name] = $prm;
			
			if ($alias)
				$this->addAlias($name, $alias);
			
			return $this;
		}

		public function get($name)
		{
			if (isset($this->aliases[$name], $this->primitives[$this->aliases[$name]]))
				return $this->primitives[$this->aliases[$name]];
			elseif (isset($this->primitives[$name]))
				return $this->primitives[$name];

			throw new ObjectNotFoundException("knows nothing about '{$name}'");
		}
		
		public function getValue($name)
		{
			return $this->get($name)->getValue();
		}
		
		public function getRawValue($name)
		{
			return $this->get($name)->getRawValue();
		}
		
		public function getActualValue($name)
		{
			return $this->get($name)->getActualValue();
		}

		public function getChoiceValue($name)
		{
			$prm	= &$this->get($name);
			$list	= &$prm->getList();
			$value	= &$prm->getValue();

			if ($value !== null)
				return $list[$value];

			return null;
		}
		
		public function getRangeMax($name)
		{
			$range = &$this->get($name)->getValue();

			return
				$range instanceof Range
					? $range->getMax()
					: null;
		}
		
		public function getRangeMin($name)
		{
			$range = &$this->get($name)->getValue();

			return
				$range instanceof Range
					? $range->getMin()
					: null;
		}

		public function getActualRangeMax($name)
		{
			$range = &$this->get($name)->getActualValue();

			return
				$range instanceof Range
					? $range->getMax()
					: null;
		}
		
		public function getActualRangeMin($name)
		{
			$range = &$this->get($name)->getActualValue();

			return
				$range instanceof Range
					? $range->getMin()
					: null;
		}
		
		public function getPrimitiveNames()
		{
			return array_keys($this->primitives);
		}
	}
?>