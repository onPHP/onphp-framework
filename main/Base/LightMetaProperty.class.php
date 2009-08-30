<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Simplified MetaClassProperty for passing information
	 * between userspace and MetaConfiguration.
	 * 
	 * @ingroup Helpers
	**/
	final class LightMetaProperty implements Stringable
	{
		private $name			= null;
		private $columnName		= null;
		private $className		= null;
		
		private $required	= false;
		private $generic	= false;
		
		/// @see MetaRelation
		private $relationId	= null;
		
		/// @see FetchStrategy
		private $strategyId	= null;
		
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
			$name, $columnName, $className, $required,
			$generic, $relationId, $strategyId
		)
		{
			$self = new self;
			
			$self->name = $name;
			$self->columnName = $columnName;
			$self->className = $className;
			
			$self->required = $required;
			$self->generic = $generic;
			
			$self->relationId = $relationId;
			$self->strategyId = $strategyId;
			
			return $self;
		}
		
		public function getName()
		{
			return $this->name;
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
		
		public function getClassName()
		{
			return $this->className;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setRequired($yrly)
		{
			$this->required = $yrly;
			
			return $this;
		}
		
		public function isGenericType()
		{
			return $this->generic;
		}
		
		public function getRelationId()
		{
			return $this->relationId;
		}
		
		public function getFetchStrategyId()
		{
			return $this->strategyId;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function setFetchStrategy(FetchStrategy $strategy)
		{
			$this->strategyId = $strategy->getId();
			
			return $this;
		}
		
		/**
		 * @return LightMetaProperty
		**/
		public function dropFetchStrategy()
		{
			$this->strategyId = null;
			
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
				.(
					is_array($this->columnName)
						? "array('".implode("', '", $this->columnName)."')"
						: "'".$this->columnName."'"
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
				.', '
				.(
					$this->strategyId
						? $this->strategyId
						: 'null'
				)
				.')';
		}
	}
?>