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
	 * @ingroup MetaBase
	**/
	class MetaClassProperty
	{
		private $class		= null;
		
		private $name		= null;
		private $columnName	= null;
		
		private $type		= null;
		private $size		= null;
		
		private $required	= false;
		private $identifier	= false;
		
		private $relation	= null;
		
		private $strategy	= null;
		
		public function __construct(
			$name,
			BasePropertyType $type,
			MetaClass $class
		)
		{
			$this->name = $name;
			
			$this->type = $type;
			
			$this->class = $class;
			
			// TODO: remove this 'convenience' 
			// because password hash can be longer than 40
			// seems to be business logic
			if ($type instanceof PasswordType)
				$this->size = 40; // strlen(sha1())
		}
		
		/**
		 * @return MetaClass
		**/
		public function getClass()
		{
			return $this->class;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return MetaClassProperty
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
		 * @return MetaClassProperty
		**/
		public function setColumnName($name)
		{
			$this->columnName = $name;
			
			return $this;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function getConvertedName()
		{
			return strtolower(
				preg_replace(':([A-Z]):', '_\1', $this->name)
			);
		}
		
		/**
		 * @return BasePropertyType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		public function getSize()
		{
			return $this->size;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return MetaClassProperty
		**/
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
					"size not allowed for '{$this->type->getClassName()}' type" 
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
		
		/**
		 * @return MetaClassProperty
		**/
		public function required()
		{
			$this->required = true;
			
			return $this;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function optional()
		{
			$this->required = false;
			
			return $this;
		}
		
		public function isIdentifier()
		{
			return $this->identifier;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function setIdentifier($really = false)
		{
			$this->identifier = ($really === true);
			
			return $this;
		}
		
		/**
		 * @return MetaRelation
		**/
		public function getRelation()
		{
			return $this->relation;
		}
		
		public function getRelationId()
		{
			if ($this->relation)
				return $this->relation->getId();
			
			return null;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function setRelation(MetaRelation $relation)
		{
			$this->relation = $relation;
			
			return $this;
		}
		
		/**
		 * @return MetaClassProperty
		**/
		public function setFetchStrategy(FetchStrategy $strategy)
		{
			$this->strategy = $strategy;
			
			return $this;
		}
		
		/**
		 * @return FetchStrategy
		**/
		public function getFetchStrategy()
		{
			return $this->strategy;
		}
		
		public function getFetchStrategyId()
		{
			if ($this->strategy)
				return $this->strategy->getId();
			
			return null;
		}
		
		public function toMethods(
			MetaClass $class,
			MetaClassProperty $holder = null
		)
		{
			if (
				!$holder
				&& $this->type instanceof ObjectType
				&& !$this->type->isGeneric()
				&& $this->type->getClass()->getPattern()
					instanceof ValueObjectPattern
			) {
				$out = null;
				
				$remote = $this->type->getClass();
				
				foreach ($remote->getProperties() as $property) {
					$out .= $property->toMethods($remote, $this);
				}
				
				return $out.$this->type->toMethods($class, $this);
			}
			
			return $this->type->toMethods($class, $this, $holder);
		}
		
		public function toPrimitive(MetaClass $class)
		{
			if (
				(
					$this->getType() instanceof ObjectType
					&& !$this->getType()->isGeneric()
				)
				|| $this->isIdentifier()
			) {
				if (
					!$this->isIdentifier() 
					&& (
						$this->getType()->getClass()->getPattern()
							instanceof EnumerationClassPattern
					)
				)
					$isEnum = true;
				else
					$isEnum = false;
				
				if ($isEnum) {
					$className = $this->getType()->getClassName();
					
					$primitiveName = $this->getName();
				} elseif ($this->isIdentifier()) {
					$className = $class->getName();
					$primitiveName = 'id';
				} else {
					$className = $this->getType()->getClassName();
					$primitiveName = $this->getName();
				}
				
				if ($isEnum) {
					$primitive =
						"\nPrimitive::enumeration('{$primitiveName}')->\n"
						."of('{$className}')->\n";
				} else {
					if (
						!$this->getRelation()
						|| (
							$this->getRelationId()
								== MetaRelation::ONE_TO_ONE
							|| $this->getRelationId()
								== MetaRelation::LAZY_ONE_TO_ONE
						)
					) {
						if (
							!$this->getType()->isGeneric()
							&& $this->getType() instanceof ObjectType
							&& (
								$this->getType()->getClass()->getPattern()
									instanceof ValueObjectPattern
							)
						) {
							$primitive = array();
							$remote = $this->getType()->getClass();
							
							foreach ($remote->getProperties() as $remoteProperty) {
								$primitive[] = $remoteProperty->toPrimitive($remote);
							}
						} else {
							$primitive =
								"\nPrimitive::identifier('{$primitiveName}')->\n";
							
							// should be specified only in childs
							if (
								!(
									$class->getType()
									&& (
										$class->getTypeId()
										== MetaClassType::CLASS_ABSTRACT
									)
									&& $this->isIdentifier()
								)
							) {
								$primitive .= "of('{$className}')->\n";
							}
							
							$id = null;
							
							// we must check remote identifier's type for limits
							if ($this->getType() instanceof ObjectType) {
								$id =
									$this->getType()->
										getClass()->
											getIdentifier();
								
							} elseif ($this->isIdentifier()) {
								$id = $this;
							}
							
							if ($id) {
								if ($limits = $id->getType()->toPrimitiveLimits())
									$primitive .= $limits."->\n";
							}
						}
					} else {
						$primitive = null;
					}
				}
				
				if ($primitive && !is_array($primitive)) {
					if ($this->getType()->hasDefault())
						$primitive .=
							"setDefault({$this->getType()->getDefault()})->\n";
					
					if ($this->isRequired())
						$primitive .= "required()\n";
					else
						$primitive .= "optional()\n";
				}
			} else {
				$required = ($this->required ? 'required' : 'optional');
				
				$size = $limits = null;
				
				if ($this->size) {
					$size = "->\nsetMax({$this->size})";
				}
				
				if ($this->type instanceof IntegerType)
					$limits = $this->type->toPrimitiveLimits();
				
				if ($limits)
					$limits = $limits."->\n";
			
				$primitive = <<<EOT

{$this->type->toPrimitive()}('{$this->name}')->
{$limits}{$required}(){$size}

EOT;
			}
			
			return $primitive;
		}
		
		public function toDaoSetter($className, $cascade = true)
		{
			$varName = $this->toVarName($className);
			$method = ucfirst($this->name);
			
			$out = null;
			
			if (!$this->type->isGeneric()) {
				
				switch ($this->relation->getId()) {
					
					case MetaRelation::ONE_TO_ONE:
					case MetaRelation::LAZY_ONE_TO_ONE:
						
						$remote = $this->type->getClass();
						
						if ($remote->getPattern() instanceof ValueObjectPattern) {
							if ($cascade) {
								$out =
									"set{$method}(\n"
									."Singleton::getInstance('{$this->type->getClassName()}DAO')->\nmakeCascade(\n"
									."Singleton::getInstance('{$this->type->getClassName()}DAO')->\nmakeSelf("
									."\$array, \$prefix), "
									."\$array, \$prefix\n)\n"
									.')';
							} else {
								$out =
									"set{$method}(\n"
									."Singleton::getInstance('{$this->type->getClassName()}DAO')->\nmakeJoiners(\n"
									."Singleton::getInstance('{$this->type->getClassName()}DAO')->\nmakeSelf("
									."\$array, \$prefix), "
									."\$array, \$prefix\n)\n"
									.')';
							}
						} elseif ($remote->getPattern() instanceof EnumerationClassPattern) {
							if ($this->required) {
								$out =
									"set{$method}("
									."new {$this->type->getClassName()}("
									."\$array[\$prefix.'{$this->getColumnName()}']"
									."))";
							} else {
								$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->getColumnName()}'])) {
	\${$varName}->set{$method}(
		new {$this->type->getClassName()}(\$array[\$prefix.'{$this->getColumnName()}'])
	);
}

EOT;
							}
						} else {
							$idName =
								$this->toVarName(
									$remote->getIdentifier()->getName()
								);
							
							if ($this->getRelationId() == MetaRelation::LAZY_ONE_TO_ONE) {
								if ($this->required) {
									$out =
										"set{$method}Id("
										."\$array[\$prefix.'{$this->getColumnName()}']"
										.')';
								} else {
									$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->getColumnName()}_{$idName}'])) {
	\${$varName}->set{$method}Id(\$array[\$prefix.'{$this->getColumnName()}_{$idName}']);
}

EOT;
								}
							} else {
								if ($cascade) {
									if ($this->required) {
										
										$out =
											"set{$method}("
											."{$this->type->getClassName()}::dao()->getById("
											."\$array[\$prefix.'{$this->getColumnName()}']"
											.'))';
										
									} else {
										
										$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->getColumnName()}'])) {
	\${$varName}->set{$method}(
		{$this->type->getClassName()}::dao()->getById(\$array[\$prefix.'{$this->getColumnName()}'])
	);
}

EOT;
									}
								} else {
									if ($this->required) {
										// avoid infinite recursion
										if ($this->type->getClassName() == $className) {
											// FIXME: prefix unused
											$out = <<<EOT
set{$method}(
	\$this->makeSelf(\$array, \$this->getJoinPrefix('{$this->getColumnName()}'))
)
EOT;
										} else
											$out = <<<EOT
set{$method}(
	{$this->type->getClassName()}::dao()->makeJoinedObject(\$array, {$this->type->getClassName()}::dao()->getJoinPrefix('{$this->getColumnName()}', \$prefix))
)
EOT;
									} else {
										// avoid infinite recursion
										if ($this->type->getClassName() == $className) {
											// FIXME: prefix unused
											$out = <<<EOT
if (isset(\$array[{$this->type->getClassName()}::dao()->getJoinPrefix('{$this->getColumnName()}').'{$idName}'])) {
	\${$varName}->set{$method}(
		\$this->makeSelf(\$array, \$this->getJoinPrefix('{$this->getColumnName()}'))
	);
}

EOT;
										} else
											$out = <<<EOT
if (isset(\$array[{$this->type->getClassName()}::dao()->getJoinPrefix('{$this->getColumnName()}', \$prefix).'{$idName}'])) {
	\${$varName}->set{$method}(
		{$this->type->getClassName()}::dao()->makeJoinedObject(\$array, {$this->type->getClassName()}::dao()->getJoinPrefix('{$this->getColumnName()}', \$prefix))
	);
}

EOT;
									}
								}
							}
						}
						
						break;
					
					case MetaRelation::ONE_TO_MANY:
					case MetaRelation::MANY_TO_MANY:
						
						return null;
					
					default:
						
						throw new UnsupportedMethodException();
				}
			} else {
				
				if ($this->type instanceof ObjectType) {
					
					$value = "new {$this->type->getClassName()}(";
					
					if ($this->type instanceof RangeType) {
						$value =
							"\n{$value}\n"
							."ArrayUtils::getArrayVar(\$array, '{$this->getColumnName()}_min'), "
							."\nArrayUtils::getArrayVar(\$array, '{$this->getColumnName()}_max')\n)\n";
					} else {
						$value .= "\$array[\$prefix.'{$this->getColumnName()}'])";
					}
				} elseif ($this->type instanceof BooleanType) {
					// MySQL returns 0/1, others - t/f
					$value = "(bool) strtr(\$array[\$prefix.'{$this->getColumnName()}'], array('f' => null))";
				} else
					$value = "\$array[\$prefix.'{$this->getColumnName()}']";
				
				if (
					$this->required
					|| $this->type instanceof RangeType
				) {
					
					$out =
						"set{$method}("
						.$value
						.")";
					
				} else {
					if ($this->type instanceof ObjectType) {
						
						$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->getColumnName()}'])) {
	\${$varName}->set{$method}(
		new {$this->type->getClassName()}(\$array[\$prefix.'{$this->getColumnName()}'])
	);
}

EOT;
					} else {
					
						$out = <<<EOT
set{$method}(
	isset(\$array[\$prefix.'{$this->getColumnName()}'])
		? {$value}
		: null
)
EOT;
					}
				}
			}
			
			return $out;
		}
		
		public function toDaoField($className)
		{
			$varName = $this->toVarName($className);
			$method = ucfirst($this->name);
			
			$out = null;
			
			if (!$this->type->isGeneric()) {
				
				switch ($this->relation->getId()) {
					
					case MetaRelation::ONE_TO_ONE:
					case MetaRelation::LAZY_ONE_TO_ONE:
						
						$remote = $this->type->getClass();
						
						if ($remote->getPattern() instanceof ValueObjectPattern) {
							// sould be handled by builder
							Assert::isTrue(false, 'unreacheble place reached');
						}
						
						$idName = $remote->getIdentifier()->getName();
						$idMethod = ucfirst($idName);
						
						if ($this->getRelationId() == MetaRelation::LAZY_ONE_TO_ONE) {
							$out .=
								"set('{$this->getColumnName()}', "
								."\${$varName}->get{$method}Id())";
						} else {
							if ($this->required)
								$out = "set('{$this->getColumnName()}', ";
							else
								$out = "set(\n'{$this->getColumnName()}', ";
						
							if ($this->required)
								$out .=
									"\${$varName}->get{$method}()->get{$idMethod}())";
							else
								$out .=
									"\n"
									."\${$varName}->get{$method}()\n"
									."? \${$varName}->get{$method}()->get{$idMethod}()\n"
									.": null\n)";
						}
						
						break;
					
					case MetaRelation::ONE_TO_MANY:
					case MetaRelation::MANY_TO_MANY:

						return null;
					
					default:
						
						throw new UnsupportedMethodException();
				}
			} else {

				if ($this->type instanceof BooleanType) {
					$set = 'setBoolean';
					$get = 'is';
				} elseif ($this->type instanceof RangeType) {
					$set = 'lazySet';
					$get = 'get';
				} else {
					$set = 'set';
					$get = 'get';
				}
				
				if ($this->type instanceof ObjectType) {
					if ($this->type instanceof RangeType)
						$out .=
							"{$set}('{$this->getColumnName()}', "
							."\${$varName}->get{$method}()";
					elseif ($this->required)
						$out .=
							"{$set}('{$this->getColumnName()}', "
							."\${$varName}->get{$method}()->toString()";
					else
						$out .=
							"{$set}(\n'{$this->getColumnName()}', "
							."\n"
							."\${$varName}->get{$method}()\n"
							."? \${$varName}->get{$method}()->toString()\n"
							.": null\n";
				} else {
					$out .=
						"{$set}('{$this->getColumnName()}', "
						."\${$varName}->{$get}{$method}()";
				}
				
				$out .= ')';
			}
			
			return $out;
		}
		
		public function getRelationColumnName()
		{
			if ($this->type instanceof ObjectType && !$this->type->isGeneric())
				if ($this->relation->getId() == MetaRelation::MANY_TO_MANY)
					$columnName = $this->type->getClass()->getTableName().'_id';
				else
					$columnName = $this->getColumnName();
			elseif ($this->type instanceof RangeType) {
				return
					array(
						$this->buildColumn("{$this->getColumnName()}_min"),
						$this->buildColumn("{$this->getColumnName()}_max")
					);
			} else
				$columnName = $this->getColumnName();
			
			return $columnName;
		}
		
		public function toColumn()
		{
			if (
				$this->getType() instanceof ObjectType
				&& !$this->getType()->isGeneric()
				&& (
					$this->getType()->getClass()->getPattern()
						instanceof ValueObjectPattern
				)
			) {
				$columns = array();
				
				$remote = $this->getType()->getClass();
				
				foreach ($remote->getProperties() as $property) {
					$columns[] = $property->buildColumn(
						$property->getRelationColumnName()
					);
				}
				
				return $columns;
			}
			
			return $this->buildColumn($this->getRelationColumnName());
		}
		
		public function toLightProperty()
		{
			return
				LightMetaProperty::make(
					$this->getName(),
					$this->getColumnName(),
					$this->getType() instanceof ObjectType
						? $this->getType()->getClassName()
						: null,
					$this->isRequired(),
					$this->getType()->isGeneric(),
					$this->getRelationId(),
					$this->getFetchStrategyId()
				);
		}
		
		private function buildColumn($columnName)
		{
			$column = <<<EOT
addColumn(
	DBColumn::create(
		{$this->type->toColumnType($this->size)}
EOT;

			if ($this->required) {
				$column .= <<<EOT
->
setNull(false)
EOT;
			}
			
			if ($this->size) {
				$column .= <<<EOT
->
setSize({$this->size})
EOT;
			}
			
			$column .= <<<EOT
,
'{$columnName}'
)
EOT;

			if ($this->identifier) {
				$column .= <<<EOT
->
setPrimaryKey(true)->
setAutoincrement(true)
EOT;
			}
			
			if ($this->type->hasDefault()) {
				
				$default = $this->type->getDefault();
				
				if (is_bool($default)) {
					if ($default)
						$default = 'true';
					else
						$default = 'false';
				}
				
				$column .= <<<EOT
->
setDefault({$default})
EOT;
			}
			
			$column .= <<<EOT

)
EOT;
			
			return $column;
		}
		
		private function toVarName($name)
		{
			return strtolower($name[0]).substr($name, 1);
		}
	}
?>