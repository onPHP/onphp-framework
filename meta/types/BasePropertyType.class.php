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
		
		public function toMethods(MetaClassProperty $property)
		{
			return
				$this->toGetter($property)
				.$this->toSetter($property);
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
		
		public function toGetter(MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'get'.ucfirst($name);
			
			$method = <<<EOT

public function {$methodName}()
{
	return \$this->{$name};
}

EOT;

			return $method;
		}
		
		public function toSetter(MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			$method = <<<EOT

public function {$methodName}(\${$name})
{
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;

			return $method;
		}
	}
?>