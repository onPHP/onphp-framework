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
	 * @ingroup MetaTypes
	**/
	class BooleanType extends BasePropertyType
	{
		public function getPrimitiveName()
		{
			return 'boolean';
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return BooleanType
		**/
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

			return 'null';
		}

		public function isMeasurable()
		{
			return false;
		}

		public function toColumnType()
		{
			return 'DataType::create(DataType::BOOLEAN)';
		}

		public function toGetter(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			$name = $property->getName();
			$camelName = ucfirst($name);

			$methodName = "is{$camelName}";
			$compatName = "get{$camelName}";
			
			if ($holder) {
				return <<<EOT

public function {$compatName}()
{
	return \$this->{$holder->getName()}->{$compatName}();
}

public function {$methodName}()
{
	return \$this->{$holder->getName()}->{$methodName}();
}

EOT;
			} else {
				return <<<EOT

public function {$compatName}()
{
	return \$this->{$name};
}

public function {$methodName}()
{
	return \$this->{$name};
}

EOT;
			}
			
			Assert::isUnreachable();
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
				if ($property->isRequired()) {
					$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}(\${$name} = false)
{
	\$this->{$name} = (\${$name} === true);

	return \$this;
}

EOT;
				} else {
					$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}(\${$name} = null)
{
	Assert::isTernaryBase(\${$name});
	
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;
				}
			}
			
			return $method;
		}
	}
?>