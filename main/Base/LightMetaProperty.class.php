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
	 * Simplified MetaClassProperty for passing information
	 * between userspace and MetaConfiguration.
	 * 
	 * @ingroup Helpers
	**/
	class LightMetaProperty implements Stringable
	{
		const UNSIGNED_FLAG = 0x1000;
		
		private static $limits = array(
			0x0002 => array(
				PrimitiveInteger::SIGNED_SMALL_MIN,
				PrimitiveInteger::SIGNED_SMALL_MAX
			),
			0x1002 => array(
				0,
				PrimitiveInteger::UNSIGNED_SMALL_MAX
			),
			0x0004 => array(
				PrimitiveInteger::SIGNED_MIN,
				PrimitiveInteger::SIGNED_MAX
			),
			0x1004 => array(
				0,
				PrimitiveInteger::UNSIGNED_MAX
			),
			0x0008 => array(
				PrimitiveInteger::SIGNED_BIG_MIN,
				PrimitiveInteger::SIGNED_BIG_MAX
			),
			0x1008 => array(
				0,
				null
			)
		);
		
		private $name		= null;
		private $columnName	= null;
		
		private $type		= null;
		private $className	= null;
		
		private $size		= null;
		
		private $min		= null;
		private $max		= null;
		
		private $required	= false;
		private $generic	= false;
		private $inner		= false;
		
		/// @see MetaRelation
		private $relationId	= null;
		
		/// @see FetchStrategy
		private $strategyId	= null;
		
		private $getter		= null;
		private $setter		= null;
		private $dropper	= null;
		
		private $identifier	= null;
		
		/**
		 * @return LightMetaProperty
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * must by in sync with InnerMetaProperty::make()
		 * 
		 * @return LightMetaProperty
		**/
		public static function fill(
			LightMetaProperty $property,
			$name, $columnName, $type, $className, $size,
			$required, $generic, $inner, $relationId, $strategyId
		)
		{
			$property->name = $name;
			
			$methodSuffix = ucfirst($name);
			$property->getter = 'get'.$methodSuffix;
			$property->setter = 'set'.$methodSuffix;
			$property->dropper = 'drop'.$methodSuffix;
			
			if ($columnName)
				$property->columnName = $columnName;
			else
				$property->columnName = $name;
			
			$property->type = $type;
			$property->className = $className;
			
			if ($size) {
				if (
					($type == 'integer')
					|| ($type == 'identifier')
					|| ($type == 'enumeration')
				) {
					$property->min = self::$limits[$size][0];
					$property->max = self::$limits[$size][1];
				} elseif ($type != 'float') { // string
					$property->max = $size;
				}
				
				$property->size = $size;
			}
			
			$property->required = $required;
			$property->generic = $generic;
			$property->inner = $inner;
			
			$property->relationId = $relationId;
			$property->strategyId = $strategyId;
			
			$property->identifier =
				$generic && $required && (
					$type == 'identifier'
				);
			
			return $property;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getColumnName()
		{
			return $this->columnName;
		}
		
		public function getGetter()
		{
			return $this->getter;
		}
		
		public function getSetter()
		{
			return $this->setter;
		}
		
		public function getDropper()
		{
			return $this->dropper;
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
		
		public function getMin()
		{
			return $this->min;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		public function getType()
		{
			return $this->type;
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
		
		public function isInner()
		{
			return $this->inner;
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
		
		public function isBuildable($array, $prefix = null)
		{
			$column = $prefix.$this->columnName;
			$exists = isset($array[$column]);
			
			if (
				$this->relationId
				|| $this->generic
			) {
				// skip collections
				if (
					($this->relationId <> MetaRelation::ONE_TO_ONE)
					&& !$this->generic
				)
					return false;
				
				if ($this->required) {
					Assert::isTrue(
						$exists,
						'required property not found: '.$this->name
					);
				} elseif (!$exists) {
					return false;
				}
			}
			
			return true;
		}
		
		public function fillMapping(array $mapping)
		{
			if (
				!$this->relationId
				|| (
					$this->relationId
					== MetaRelation::ONE_TO_ONE
				) || (
					$this->strategyId
					== FetchStrategy::LAZY
				)
			) {
				$mapping[$this->name] = $this->columnName;
			}
			
			return $mapping;
		}
		
		/**
		 * @return Form
		**/
		public function fillForm(Form $form, $prefix = null)
		{
			$prm =
				call_user_func(
					array('Primitive', $this->type),
					$prefix.$this->name
				);
			
			if (null !== ($min = $this->getMin()))
				$prm->setMin($min);
			
			if (null !== ($max = $this->getMax()))
				$prm->setMax($max);
			
			if ($prm instanceof IdentifiablePrimitive)
				$prm->of($this->className);
			
			if ($this->required)
				$prm->required();
			
			return $form->add($prm);
		}
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		public function fillQuery(
			InsertOrUpdateQuery $query,
			Prototyped $object
		)
		{
			if (
				$this->relationId
				|| $this->generic
			) {
				// skip collections
				if (
					($this->relationId <> MetaRelation::ONE_TO_ONE)
					&& !$this->generic
				)
					return $query;
				
				$value = $object->{$this->getter}();
				
				if ($this->type == 'binary') {
					$query->set($this->columnName, new DBBinary($value));
				} else {
					$query->lazySet($this->columnName, $value);
				}
			}
			
			return $query;
		}
		
		public function toValue(ProtoDAO $dao = null, $array, $prefix = null)
		{
			$raw = $array[$prefix.$this->columnName];
			
			if ($this->type == 'binary') {
				return DBPool::getByDao($dao)->getDialect()->unquoteBinary($raw);
			}
			
			if ($this->className == 'HttpUrl') {
				return HttpUrl::create()->parse($raw);
			}
			
			if (
				!$this->identifier
				&& $this->generic
				&& $this->className
			) {
				return call_user_func(array($this->className, 'create'), $raw);
			} elseif (
				!$this->identifier
				&& $this->className
			) {
				// BOVM: prevents segfault on >=php-5.2.5
				Assert::classExists($this->className);
				
				if (!is_subclass_of($this->className, 'Enumeration')) {
					$remoteDao = call_user_func(array($this->className, 'dao'));
					
					$joinPrefix = $remoteDao->getJoinPrefix(
						$this->columnName,
						$prefix
					);
					
					$joined = (
						($this->strategyId == FetchStrategy::JOIN)
						|| isset($array[$joinPrefix.$remoteDao->getIdName()])
					);
					
					if ($joined) {
						return $remoteDao->makeObject($array, $joinPrefix);
					} else {
						// will be fetched later
						// by AbstractProtoClass::fetchEncapsulants
						$object = new $this->className;
						$object->setId($raw);
						
						return $object;
					}
				} else {
					return new $this->className($raw);
				}
			}
			
			// veeeeery "special" handling, by tradition.
			// MySQL returns 0/1, others - t/f
			if ($this->type == 'boolean') {
				return (bool) strtr($raw, array('f' => null));
			}
			
			return $raw;
		}
		
		public function isIdentifier()
		{
			return $this->identifier;
		}
		
		final public function toString()
		{
			return
				get_class($this).'::fill('
				.'new '.get_class($this).'(), '
				."'{$this->name}', "
				.(
					($this->columnName <> $this->name)
						? "'{$this->columnName}'"
						: 'null'
				)
				.', '
				."'{$this->type}', "
				.(
					$this->className
						? "'{$this->className}'"
						: 'null'
				)
				.', '
				.(
					$this->size
						? $this->size
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
					$this->inner
						? 'true'
						: 'false'
				)
				.', '
				.(
					$this->relationId
						? $this->relationId
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