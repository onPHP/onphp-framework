<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class MetaClassProperty
	{
		private $name		= null;
		private $dumbName	= null;
		private $type		= null;
		private $size		= null;
		
		private $required	= false;
		private $identifier	= false;
		
		private $relation	= null;
		
		public function __construct($name, BasePropertyType $type)
		{
			$this->name = $name;
			$this->dumbName = strtolower(
				preg_replace(':([A-Z]):', '_\1', $name)
			);
			
			$this->type = $type;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getDumbName()
		{
			return $this->dumbName;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		public function getSize()
		{
			return $this->size;
		}
		
		public function setSize($size)
		{
			Assert::isInteger(
				$size,
				'only integers allowed in size parameter'
			);
			
			if ($this->type->isMeasurable())
				$this->size = $size;
			else
				throw new WrongArgumentException(
					"size not allowed for '{$this->type->getName()}' type" 
				);
			
			return $this;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		public function isOptional()
		{
			return !$this->required;
		}
		
		public function required()
		{
			$this->required = true;
			
			return $this;
		}
		
		public function optional()
		{
			$this->required = false;
			
			return $this;
		}
		
		public function isIdentifier()
		{
			return $this->identifier;
		}
		
		public function setIdentifier($really = false)
		{
			$this->identifier = ($really === true);
			
			return $this;
		}
		
		public function getRealtion()
		{
			return $this->relation;
		}
		
		public function setRelation(MetaRelation $relation)
		{
			$this->relation = $relation;
			
			return $this;
		}
		
		public function toMethods()
		{
			return $this->type->toMethods($this->name);
		}
	}
?>