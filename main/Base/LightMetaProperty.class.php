<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Simplified MetaClassProperty for passing information
	 * between userspace and MetaConfiguration.
	 * 
	 * @ingroup Helpers
	**/
	final class LightMetaProperty implements Stringable
	{
		protected $name			= null;
		protected $columnName	= null;
		protected $columnIdName	= null;
		protected $className	= null;
		
		protected $required	= false;
		protected $generic	= false;
		
		protected $relationId	= null;
		
		/**
		 * @return LightMetaProperty
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public static function make(
			$name, $columnName, $columnIdName, $className,
			$required, $generic, $relationId
		)
		{
			$self = new self;
			
			$self->name = $name;
			$self->columnName = $columnName;
			$self->columnIdName = $columnIdName;
			$self->className = $className;
			
			$self->required = $required;
			$self->generic = $generic;
			
			$self->relationId = $relationId;
			
			return $self;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getColumnName()
		{
			return $this->columnName;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setColumnName($name)
		{
			$this->columnName = $name;
			
			return $this;
		}
		
		public function getColumnIdName()
		{
			return $this->columnIdName;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setColumnIdName($name)
		{
			$this->columnIdName = $name;
			
			return $this;
		}
		
		public function getClassName()
		{
			return $this->className;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setClassName($name)
		{
			$this->className = $name;
			
			return $this;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setRequired($required = false)
		{
			$this->required = $required;
			
			return $this;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		public function isGenericType()
		{
			return $this->generic;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setGenericType($generic = true)
		{
			$this->generic = $generic;
			
			return $this;
		}
		
		public function getRelationId()
		{
			return $this->relationId;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setRelation(MetaRelation $relation)
		{
			$this->relationId = $relation->getId();
			
			return $this;
		}
		
		public function getContainerName($holderName)
		{
			return $holderName.ucfirst($this->getName()).'DAO';
		}
		
		public function toString()
		{
			return
				'LightMetaProperty::make('
				."'{$this->name}', "
				."'{$this->columnName}', "
				.(
					$this->columnIdName
						? "'{$this->columnIdName}'"
						: 'null'
				)
				.', '
				.(
					$this->className
						? "'{$this->className}'"
						: 'null'
				)
				.', '
				.(
					$this->required
						? 'true'
						: 'false'
				)
				.', '
				.(
					$this->generic
						? 'true'
						: 'false'
				)
				.', '
				.(
					$this->relationId
						? "'".$this->relationId."'"
						: 'null'
				)
				.')';
		}
	}
?>