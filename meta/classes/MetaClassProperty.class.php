<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
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
			
			if ($type instanceof PasswordType)
				$this->size = 40; // strlen(sha1())
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
					"size not allowed for '{$this->type->getClass()}' type"
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
		
		public function getRelation()
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
			return $this->type->toMethods($this);
		}
		
		public function toPrimitive()
		{
			$required = ($this->required ? 'required' : 'optional');
			
			$size = null;
			
			if ($this->size) {
				$size = "->\nsetMax({$this->size})";
			}
			
			return <<<EOT

{$this->type->toPrimitive()}('{$this->name}')->
$required()$size

EOT;
		}
		
		public function toDaoSetter($className)
		{
			$varName = $this->toVarName($className);
			$method = ucfirst($this->name);
			
			$out = null;
			
			if (!$this->type->isGeneric()) {
				
				switch ($this->relation->getId()) {
					
					case MetaRelation::ONE_TO_ONE:
						
						$remote =
							MetaConfiguration::me()->getClassByName(
								$this->type->getClass()
							);
						
						$idName =
							$this->toVarName(
								$remote->getIdentifier()->getName()
							);
						
						if ($remote->getPattern() instanceof EnumerationClassPattern) {
							if ($this->required) {
								$out =
									"set{$method}("
									."new {$this->type->getClass()}("
									."\$array[\$prefix.'{$this->dumbName}_id']"
									."))";
							} else {
								$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->dumbName}_id'])) {
	\${$varName}->set{$method}(
		new {$this->type->getClass()}(\$array[\$prefix.'{$this->dumbName}_id'])
	);
}

EOT;
							}
						} else {
							if ($this->required) {
								
								$out =
									"set{$method}("
									."{$this->type->getClass()}::dao()->getById("
									."\$array[\$prefix.'{$this->dumbName}_{$idName}']"
									."))";
								
							} else {
								
								$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->dumbName}_{$idName}'])) {
	\${$varName}->set{$method}(
		{$this->type->getClass()}::dao()->getById(\$array[\$prefix.'{$this->dumbName}_{$idName}'])
	);
}

EOT;
								
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
					
					$value = "new {$this->type->getClass()}(";
					
					if ($this->type instanceof RangeType) {
						$value =
							"\n{$value}\n"
							."ArrayUtils::getArrayVar(\$array, '{$this->dumbName}_min'), "
							."\nArrayUtils::getArrayVar(\$array, '{$this->dumbName}_max')\n)\n";
					} else {
						$value .= "\$array[\$prefix.'{$this->dumbName}'])";
					}
				} elseif ($this->type instanceof BooleanType) {
					// FIXME: it's plain ugly
					if (defined('DB_CLASS') && DB_CLASS == 'MySQL')
						$value = "\$array[\$prefix.'{$this->dumbName}'] ? true : false";
					else
						$value = "\$array[\$prefix.'{$this->dumbName}'][0] == 't'";
				} else
					$value = "\$array[\$prefix.'{$this->dumbName}']";
				
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
if (isset(\$array[\$prefix.'{$this->dumbName}'])) {
	\${$varName}->set{$method}(
		new {$this->type->getClass()}(\$array[\$prefix.'{$this->dumbName}'])
	);
}

EOT;
					} else {
					
						$out = <<<EOT
set{$method}(
	isset(\$array[\$prefix.'{$this->dumbName}'])
		? {$value}
		: null
)
EOT;
					}
				}
			}
			
			return $out;
		}
		
		public function toDaoField($className, $indent = 5)
		{
			$varName = $this->toVarName($className);
			$method = ucfirst($this->name);
			
			$out = null;
			
			if (!$this->type->isGeneric()) {
				
				switch ($this->relation->getId()) {
					
					case MetaRelation::ONE_TO_ONE:
						
						$remote =
							MetaConfiguration::me()->getClassByName(
								$this->type->getClass()
							);
						
						$idName = $remote->getIdentifier()->getName();
						$idMethod = ucfirst($idName);

						if ($this->required)
							$out = "set('{$this->dumbName}_{$idName}', ";
						else
							$out = "set(\n'{$this->dumbName}_{$idName}', ";
						
						if ($remote->getPattern() instanceof EnumerationClassPattern) {
							if ($this->required)
								$out .= "\${$varName}->get{$method}()->getId())";
							else
								$out .=
									"\n"
									."\${$varName}->get{$method}()\n"
									."? \${$varName}->get{$method}()->get{$idMethod}()\n"
									.": null\n)";
						} else {
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
							"{$set}('{$this->dumbName}', "
							."\${$varName}->get{$method}()";
					elseif ($this->required)
						$out .=
							"{$set}('{$this->dumbName}', "
							."\${$varName}->get{$method}()->toString()";
					else
						$out .=
							"{$set}(\n'{$this->dumbName}', "
							."\n"
							."\${$varName}->get{$method}()\n"
							."? \${$varName}->get{$method}()->toString()\n"
							.": null\n";
				} else {
					$out .=
						"{$set}('{$this->dumbName}', "
						."\${$varName}->{$get}{$method}()";
				}
				
				$out .= ')';
			}
			
			return $out;
		}
		
		public function toColumn()
		{
			if ($this->type instanceof ObjectType && !$this->type->isGeneric())
				$dumbName = "{$this->dumbName}_id";
			elseif ($this->type instanceof RangeType) {
				return
					array(
						$this->buildColumn("{$this->dumbName}_min"),
						$this->buildColumn("{$this->dumbName}_max")
					);
			} else
				$dumbName = $this->dumbName;
			
			return $this->buildColumn($dumbName);
		}
		
		private function buildColumn($dumbName)
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
'{$dumbName}'
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
				
				if ($this->type instanceof BooleanType) {
					if ($default)
						$default = 'true';
					else
						$default = 'false';
				} elseif ($this->type instanceof StringType) {
					$default = "'{$default}'";
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