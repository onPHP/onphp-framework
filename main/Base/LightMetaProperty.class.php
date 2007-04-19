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
	class LightMetaProperty implements Stringable
	{
		private static $limits = array(
			'SmallInteger' => array(
				PrimitiveInteger::SIGNED_SMALL_MIN,
				PrimitiveInteger::SIGNED_SMALL_MAX
			),
			'UnsignedSmallInteger' => array(
				0,
				PrimitiveInteger::UNSIGNED_SMALL_MAX
			),
			'Integer' => array(
				PrimitiveInteger::SIGNED_MIN,
				PrimitiveInteger::SIGNED_MAX
			),
			'UnsignedInteger' => array(
				0,
				PrimitiveInteger::UNSIGNED_MAX
			)
		);
		
		private $name			= null;
		private $columnName		= null;
		
		private $type			= null;
		private $className		= null;
		
		private $size			= null;
		
		private $required	= false;
		private $generic	= false;
		private $inner		= false;
		
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
		 * must by in sync with InnerMetaProperty::make()
		 * 
		 * @return LightMetaProperty
		**/
		public static function fill(
			$property, $name, $columnName, $type, $className, $size,
			$required, $generic, $inner, $relationId, $strategyId
		)
		{
			$property->name = $name;
			$property->columnName = $columnName;
			
			$property->type = $type;
			$property->className = $className;
			
			$property->size = $size;
			
			$property->required = $required;
			$property->generic = $generic;
			$property->inner = $inner;
			
			$property->relationId = $relationId;
			$property->strategyId = $strategyId;
			
			return $property;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getColumnName()
		{
			if ($this->columnName)
				return $this->columnName;
			
			return $this->name;
		}
		
		public function getGetter()
		{
			return 'get'.ucfirst($this->getName());
		}
		
		public function getSetter()
		{
			return 'set'.ucfirst($this->getName());
		}
		
		public function getDropper()
		{
			return 'drop'.ucfirst($this->getName());
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
		
		public function getSize()
		{
			return $this->size;
		}
		
		public function getMin()
		{
			return $this->getLimit(0);
		}
		
		public function getMax()
		{
			if ($size = $this->getSize())
				return $size;
			
			return $this->getLimit(1);
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
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
			$this->strategyId = $strategy;
			
			return $this;
		}
		
		public function getContainerName($holderName)
		{
			return $holderName.ucfirst($this->getName()).'DAO';
		}
		
		public function isBuildable($array, $prefix = null)
		{
			$column = $prefix.$this->getColumnName();
			$exists = isset($array[$column]);
			
			if (
				$this->getRelationId()
				|| $this->isGenericType()
			) {
				// skip collections
				if (
					($this->getRelationId() <> MetaRelation::ONE_TO_ONE)
					&& !$this->isGenericType()
				)
					return false;
				
				if ($this->isRequired()) {
					Assert::isTrue(
						$exists,
						'required property not found: '.$this->getName()
					);
				} elseif (!$exists) {
					return false;
				}
			}
			
			return true;
		}
		
		public function processMapping(array $mapping)
		{
			if (
				!$this->getRelationId()
				|| (
					$this->getRelationId()
					== MetaRelation::ONE_TO_ONE
				) || (
					$this->getFetchStrategyId()
					== FetchStrategy::LAZY
				)
			) {
				$mapping[$this->getName()] = $this->getColumnName();
			}
			
			return $mapping;
		}
		
		/**
		 * @return Form
		**/
		public function processFormExport(Form $form, $object, $ignoreNull = true)
		{
			if (!$form->primitiveExists($this->getName()))
				return $form;
			
			$setter = $this->getSetter();
			$value = $form->getValue($this->getName());
			
			if (
				!$ignoreNull || ($value !== null)
			) {
				if ($this->isIdentifier()) {
					$value = $value->getId();
				}
				
				if (
					($value === null)
						&& method_exists($object, $dropper = $this->getDropper())
						&& (
							!$this->getRelationId()
							|| ($this->getRelationId() == MetaRelation::ONE_TO_ONE)
						)
				) {
					$dropper = $this->getDropper();
					
					$object->$dropper();
					
					return $form;
				} elseif (
					($this->getRelationId() == MetaRelation::ONE_TO_MANY)
					|| ($this->getRelationId() == MetaRelation::MANY_TO_MANY)
				) {
					if ($value === null)
						$value = array();
					
					$getter = $this->getGetter();
					$object->$getter()->setList($value);
					
					return $form;
				}
				
				$object->$setter($value);
			}
			
			return $form;
		}
		
		/**
		 * @return Form
		**/
		public function processFormImport(
			$object, Form $form, $ignoreNull = true, $prefix = null
		)
		{
			if (
				($this->getFetchStrategyId() == FetchStrategy::LAZY)
				|| !$form->primitiveExists($this->getName())
			)
				return $form;
			
			$getter = $this->getGetter();
			
			$value = $object->$getter();
			
			if (!$ignoreNull || ($value !== null)) {
				$form->importValue($this->getName(), $value);
			}
			
			return $form;
		}
		
		/**
		 * @return Form
		**/
		public function processForm(Form $form, $prefix = null)
		{
			$prm =
				call_user_func(
					array('Primitive', $this->getType()),
					$prefix.$this->getName()
				);
			
			if ($min = $this->getMin())
				$prm->setMin($min);
			
			if ($max = $this->getMax())
				$prm->setMax($max);
			
			if ($prm instanceof IdentifiablePrimitive)
				$prm->of($this->getClassName());
			
			if ($this->isRequired())
				$prm->required();
			
			return $form->add($prm);
		}
		
		public function processQuery(
			InsertOrUpdateQuery $query,
			Prototyped $object
		)
		{
			$getter = $this->getGetter();
			
			if (
				$this->getRelationId()
				|| $this->isGenericType()
			) {
				// skip collections
				if (
					($this->getRelationId() <> MetaRelation::ONE_TO_ONE)
					&& !$this->isGenericType()
				)
					return $query;
				
				$query->lazySet($this->getColumnName(), $object->$getter());
			}
			
			return $query;
		}
		
		public function toValue(ProtoDAO $dao = null, $array, $prefix = null)
		{
			if ($dao && ($this->getFetchStrategyId() == FetchStrategy::JOIN))
				$raw = $array[$dao->getJoinPrefix($this->getColumnName(), $prefix)];
			else
				$raw = $array[$prefix.$this->getColumnName()];
			
			if (
				!$this->isIdentifier()
				&& $this->isGenericType()
				&& $this->getClassName()
			) {
				return call_user_func(array($this->getClassName(), 'create'), $raw);
			} elseif (!$this->isIdentifier() && $this->getClassName()) {
				$remoteDao = call_user_func(array($this->getClassName(), 'dao'));
				
				if ($this->getFetchStrategyId() == FetchStrategy::JOIN) {
					return $remoteDao->makeJoinedObject(
						$array,
						$remoteDao->getJoinPrefix($this->getColumnName(), $prefix)
					);
				} else {
					return $remoteDao->getById($raw);
				}
			}
			
			// veeeeery "special" handling, by tradition.
			// MySQL returns 0/1, others - t/f
			if ($this->getType() == 'boolean') {
				return (bool) strtr($raw, array('f' => null));
			}
			
			return $raw;
		}
		
		final public function toString()
		{
			return
				get_class($this).'::fill('
				.'new '.get_class($this).'(), '
				."'{$this->name}', "
				.(
					$this->columnName
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
		
		public function isIdentifier()
		{
			return (
				$this->generic && $this->required && (
					$this->type == 'identifier'
				)
			);
		}
		
		private function getLimit($whichOne)
		{
			return
				isset(self::$limits[$this->type])
					? self::$limits[$this->type][$whichOne]
					: null;
		}
	}
?>