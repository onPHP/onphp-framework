<?php
/***************************************************************************
 *   Copyright (C) 2007 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	final class FixedLengthStringType extends StringType
	{
		public function toSetter(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			$assert = <<<EOT
Assert::isTrue(
	(\${$name} === null)
	|| (mb_strlen(\${$name}) == {$property->getSize()})
);
EOT;
			
			if ($holder) {
				return <<<EOT

/**
 * @return {$holder->getClass()->getName()}
**/
public function {$methodName}(\${$name})
{
	{$assert}
	
	\$this->{$holder->getName()}->{$methodName}(\${$name});

	return \$this;
}

EOT;
			} else {
				return <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}(\${$name})
{
	{$assert}
	
	\$this->{$name} = \${$name};

	return \$this;
}

EOT;
			}
			
			Assert::isUnreachable();
		}
		
		public function toColumnType($length = null)
		{
			return 'DataType::create(DataType::CHAR)';
		}
	}
?>