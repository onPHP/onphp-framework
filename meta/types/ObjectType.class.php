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
	 * @ingroup Types
	**/
	class ObjectType extends BasePropertyType
	{
		private $class = null;
		
		public function __construct($class)
		{
			$this->class = $class;
		}
		
		public function getClass()
		{
			return $this->class;
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
		
		public function toMethods(MetaClassProperty $property)
		{
			return
				parent::toMethods($property)
				.$this->toDropper($property);
		}
		
		public function toSetter(MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			$method = <<<EOT

public function {$methodName}({$this->class} \${$name})
{
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;

			return $method;
		}
		
		public function toDropper(MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'drop'.ucfirst($name);
			
			$method = <<<EOT

public function {$methodName}()
{
	\$this->{$name} = null;

	return \$this;
}

EOT;

			return $method;
		}
		
		public function toPrimitive()
		{
			throw new UnsupportedMethodException();
		}
		
		public function toColumnType()
		{
			return
				MetaConfiguration::me()->
					getClassByName($this->class)->
						getIdentifier()->
							getType()->toColumnType();
		}
	}
?>