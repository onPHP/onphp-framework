<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

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
		
		public function toMethods($name)
		{
			return
				parent::toMethods($name)
				.$this->toDropper($name);
		}
		
		public function toSetter($name)
		{
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
		
		public function toDropper($name)
		{
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