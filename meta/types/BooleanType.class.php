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

		public function toGetter(MetaClassProperty $property)
		{
			$name = $property->getName();
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

		public function toSetter(MetaClassProperty $property)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			if ($property->isRequired()) {
				$method = <<<EOT

public function {$methodName}(\${$name} = false)
{
	\$this->{$name} = (\${$name} === true);

	return \$this;
}

EOT;
			} else {
				$method = <<<EOT

public function {$methodName}(\${$name} = null)
{
	try {
		Assert::isTernaryBase(\${$name});
		\$this->{$name} = \${$name};
	}
	catch (WrongArgumentException \$e) {/*_*/}

	return \$this;
}

EOT;
			}
			
			return $method;
		}
	}
?>