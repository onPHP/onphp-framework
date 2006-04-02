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

	abstract class BasePropertyType
	{
		abstract public function getDeclaration();
		abstract public function isMeasurable();
		abstract public function toColumnType();
		abstract public function toPrimitive();
		
		public function isGeneric()
		{
			return true;
		}
		
		public function toMethods($name)
		{
			return
				$this->toGetter($name)
				.$this->toSetter($name);
		}
		
		public function toGetter($name)
		{
			$methodName = 'get'.ucfirst($name);
			
			$method = <<<EOT

public function {$methodName}()
{
	return \$this->{$name};
}

EOT;

			return $method;
		}
		
		public function toSetter($name)
		{
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