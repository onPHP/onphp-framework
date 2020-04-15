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
use OnPHP\Core\Form\Form;
use OnPHP\Main\EntityProto\EntityProto;
use OnPHP\Main\EntityProto\PrototypedSetter;

abstract class FormMutator extends PrototypedSetter
{
	private $getter = null;

	public function __construct(EntityProto $proto, &$object)
	{
		Assert::isInstance($object, Form::class);

		return parent::__construct($proto, $object);
	}

	/**
	 * @return FormGetter
	**/
	public function getGetter()
	{
		if (!$this->getter) {
			$this->getter = new FormGetter($this->proto, $this->object);
		}

		return $this->getter;
	}
}
?>
