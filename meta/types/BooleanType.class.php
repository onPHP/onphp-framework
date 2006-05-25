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

	/**
	 * @ingroup Types
	**/
	class BooleanType extends BasePropertyType
	{
		public function setDefault($default)
		{
			static $boolean = array('true' => true, 'false' => false);
			
			if (!isset($boolean[$default]))
				throw new WrongArgumentException(
					"strange default value given - '{$default}'"
				);

			$this->default = $boolean[$default];
			
			return $this;
		}
		
		public function getDeclaration()
		{
			if ($this->hasDefault())
				return
					$this->default
						? 'true'
						: 'false';
			
			return 'false';
		}
		
		public function isMeasurable()
		{
			return false;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::BOOLEAN)';
		}
		
		public function toPrimitive()
		{
			return 'Primitive::boolean';
		}
		
		public function toGetter($name)
		{
			$camelName = ucfirst($name);
			
			$methodName = "is{$camelName}";
			$compatName = "get{$camelName}";
			
			$method = <<<EOT

public function {$compatName}()
{
	return \$this->{$name};
}

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

public function {$methodName}(\${$name} = false)
{
	\$this->{$name} = (\${$name} === true);

	return \$this;
}

EOT;

			return $method;
		}
	}
?>