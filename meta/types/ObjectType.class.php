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
	 * @ingroup Types
	**/
	class ObjectType extends BasePropertyType
	{
		private $className = null;
		
		public function __construct($className)
		{
			$this->className = $className;
		}
		
		/**
		 * @return MetaClass
		**/
		public function getClass()
		{
			return MetaConfiguration::me()->getClassByName($this->className);
		}
		
		public function getClassName()
		{
			return $this->className;
		}
		
		public function getDeclaration()
		{
			return 'null';
		}
		
		public function isGeneric()
		{
			return false;
		}
		
		public function isMeasurable()
		{
			return false;
		}
		
		public function toMethods(MetaClass $class, MetaClassProperty $property)
		{
			return
				parent::toMethods($class, $property)
				.$this->toDropper($class, $property);
		}
		
		public function toGetter(MetaClass $class, MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'get'.ucfirst($name);
			
			if ($property->getRelationId() == MetaRelation::LAZY_ONE_TO_ONE) {
				$className = $property->getType()->getClassName();
				
				if ($property->isRequired()) {
					$method = <<<EOT

public function {$methodName}()
{
	if (!\$this->{$name}) {
		\$this->{$name} = {$className}::dao()->getById(\$this->{$name}Id);
	}

	return \$this->{$name};
}

EOT;
				} else {
					$method = <<<EOT

/**
 * @return {$this->class}
**/
public function {$methodName}()
{
	if (!\$this->{$name} && \$this->{$name}Id) {
		\$this->{$name} = {$className}::dao()->getById(\$this->{$name}Id);
	}
	
	return \$this->{$name};
}

EOT;
				}
				
				$method .= <<<EOT

public function {$methodName}Id()
{
	return \$this->{$name}Id;
}

EOT;
			} else {
				$method = <<<EOT

/**
 * @return {$this->className}
**/
public function {$methodName}()
{
	return \$this->{$name};
}

EOT;
			}
			
			return $method;
		}
		
		public function toSetter(MetaClass $class, MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			if ($property->getRelationId() == MetaRelation::LAZY_ONE_TO_ONE) {
				$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}({$this->className} \${$name})
{
	\$this->{$name} = \${$name};
	\$this->{$name}Id = \${$name}->getId();

	return \$this;
}

/**
 * @return {$class->getName()}
**/
public function {$methodName}Id(\$id)
{
	\$this->{$name} = null;
	\$this->{$name}Id = \$id;

	return \$this;
}

EOT;
			} else {
				$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}({$this->className} \${$name})
{
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;
			}
			
			return $method;
		}
		
		public function toDropper(MetaClass $class, MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'drop'.ucfirst($name);
			
			if ($property->getRelationId() == MetaRelation::LAZY_ONE_TO_ONE) {
				$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}()
{
	\$this->{$name} = null;
	\$this->{$name}Id = null;

	return \$this;
}

EOT;
			} else {
				$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}()
{
	\$this->{$name} = null;

	return \$this;
}

EOT;
			}
			
			return $method;
		}
		
		public function toPrimitive()
		{
			throw new UnsupportedMethodException();
		}
		
		public function toColumnType()
		{
			return $this->getClass()->getIdentifier()->getType()->toColumnType();
		}
	}
?>