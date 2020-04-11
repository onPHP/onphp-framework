<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\EntityProto\Accessor;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Form\Form;
use OnPHP\Main\EntityProto\EntityProto;
use OnPHP\Main\EntityProto\PrototypedGetter;

final class FormGetter extends PrototypedGetter
{
	public function __construct(EntityProto $proto, &$object)
	{
		Assert::isInstance($object, Form::class);

		return parent::__construct($proto, $object);
	}

	public function get($name)
	{
		if (!isset($this->mapping[$name]))
			throw new WrongArgumentException(
				"knows nothing about property '{$name}'"
			);

		$primitive = $this->mapping[$name];

		return $this->object->getValue($primitive->getName());
	}
}
?>
