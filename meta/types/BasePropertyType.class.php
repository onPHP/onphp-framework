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
	 * @ingroup Types
	**/
	abstract class BasePropertyType
	{
		abstract public function getDeclaration();
		abstract public function isMeasurable();
		abstract public function toColumnType();
		abstract public function toPrimitive();
		
		protected $default = null;
		
		public function isGeneric()
		{
			return true;
		}
		
		public function toMethods(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			return
				$this->toGetter($class, $property, $holder)
				.$this->toSetter($class, $property, $holder);
		}
		
		public function hasDefault()
		{
			return ($this->default !== null);
		}
		
		public function getDefault()
		{
			return $this->default;
		}
		
		public function setDefault($default)
		{
			throw new UnsupportedMethodException(
				'only generic non-object types can have default values atm'
			);
		}
		
		public function toGetter(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			if ($holder)
				$name = $holder->getName().'->get'.ucfirst($property->getName()).'()';
			else
				$name = $property->getName();
			
			$methodName = 'get'.ucfirst($property->getName());
			
			return <<<EOT

public function {$methodName}()
{
	return \$this->{$name};
}

EOT;
		}
		
		public function toSetter(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			if ($holder) {
				return <<<EOT

/**
 * @return {$holder->getClass()->getName()}
**/
public function {$methodName}(\${$name})
{
	\$this->{$holder->getName()}->{$methodName}(\${$name});

	return \$this;
}

EOT;
			} else {
				if ($class->getPattern() instanceof DtoClassPattern)
					$classNamePrefix = 'Dto';
				else
					$classNamePrefix = null;

				return <<<EOT

/**
 * @return {$classNamePrefix}{$class->getName()}
**/
public function {$methodName}(\${$name})
{
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;
			}

			Assert::isUnreachable();
		}
		
		public function getHint()
		{
			return null;
		}
	}
?>