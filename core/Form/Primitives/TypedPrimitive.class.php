<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
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
	abstract class TypedPrimitive extends BasePrimitive
	{
		protected $atom = null;
		
		abstract public function getTypeName();
		abstract public function isObjectType();
		
		public function __construct($name)
		{
			parent::__construct($name);
			
			$typeName = $this->getTypeName();
			
			$this->atom = new $typeName;
		}
		
		/**
		 * @return TypedPrimitive
		**/
		public function clean()
		{
			parent::clean();
			
			$this->atom->dropValue();
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			try {
				$this->atom->
					setValue($scope[$this->name]);
				
				if ($this->isObjectType())
					$this->value = clone $this->atom;
				else
					$this->value = $this->atom->getValue();
			} catch (WrongArgumentException $e) {
				return false;
			} catch (OutOfRangeException $e) {
				return false;
			}
			
			return true;
		}
	}
?>