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
		
		public function getRelation()
		{
			return $this->relation;
		}
		
		public function toMethods()
		{
			return $this->type->toMethods($this->name);
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
						
						$idName =
							$this->toVarName(
								MetaConfiguration::me()->
								getClassByName(
									$this->type->getClass()
								)->
									getIdentifier()->
										getName()
							);
						
						if ($this->required) {
							
							$out =
								"set{$method}("
								."{$this->type->getClass()}::dao()->getById("
								."\$array[\$prefix.'{$this->dumbName}_{$idName}']"
								."))";
							
						} else {
							
							$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->dumbName}_{$idName}']))
	\${$varName}->set{$method}(
		{$this->type->getClass()}::dao()->getById(\$array[\$prefix.'{$this->dumbName}_{$idName}'])
	);

EOT;
							
						}
						
						break;
					
					case MetaRelation::ENUMERATION:
						
						if ($this->required) {
							$out =
								"set{$method}("
								."new {$this->type->getClass()}("
								."\$array[\$prefix.'{$this->dumbName}_id']"
								."))";
						} else {
							$out = <<<EOT
if (isset(\$array[\$prefix.'{$this->dumbName}_id']))
	\${$varName}->set{$method}(
		new {$this->type->getClass()}(\$array[\$prefix.'{$this->dumbName}_id'])
	);

EOT;
						}
						
						break;
					
					default:
						
						throw new UnsupportedMethodException();
				}
			} else {
				
				if ($this->type instanceof ObjectType) {
					
					$value =
						"new {$this->type->getClass()}("
						."\$array[\$prefix.'{$this->dumbName}'])";
					
				} else
					$value = "\$array[\$prefix.'{$this->dumbName}']";
				
				if ($this->required) {
					
					$out =
						"set{$method}("
						.$value
						.")";
					
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
						
						$idName =
							$this->toVarName(
								MetaConfiguration::me()->
								getClassByName(
									$this->type->getClass()
								)->
									getIdentifier()->
										getName()
							);
						
						$out =
							"set('{$this->dumbName}_{$idName}', ";
						
						if ($this->required)
							$out .=
								"\${$varName}->get{$method}()->get{$idName}())";
						else
							$out .=
								"\n"
								."\${$varName}->get{$method}()\n"
								."? \${$varName}->get{$method}()->get{$idName}()\n"
								.": null\n)";
						
						break;
					
					case MetaRelation::ENUMERATION:
						
						$out = "set('{$this->dumbName}_id', ";
						
						if ($this->required)
							$out .= "\${$varName}->get{$method}->getId()";
						else
							$out .=
								"\n"
								."\${$varName}->get{$method}()\n"
								."? \${$varName}->get{$method}()->get{$idName}()\n"
								.": null\n)";
						
						break;
					
					default:
						
						throw new UnsupportedMethodException();
				}
			} else {

				$out = "set('{$this->dumbName}', ";
				
				if ($this->type instanceof ObjectType) {
					if ($this->required)
						$out .=
							"\${$varName}->get{$method}()->toString()";
					else
						$out .=
							"\n"
							."\${$varName}->get{$method}()\n"
							."? \${$varName}->get{$method}()->toString()\n"
							.": null\n";
				} else {
					$out .=	"\${$varName}->get{$method}()";
				}
				
				$out .= ')';
			}
			
			return $out;
		}
		
		public function toColumn()
		{
			if ($this->type instanceof ObjectType && !$this->type->isGeneric())
				$dumbName = "{$this->dumbName}_id";
			else
				$dumbName = $this->dumbName;
			
			$column = <<<EOT
addColumn(
	DBColumn::create(
		{$this->type->toColumnType()}
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