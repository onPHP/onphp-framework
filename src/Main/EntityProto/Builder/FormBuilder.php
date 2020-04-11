<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\EntityProto\Builder;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitives\PrimitiveForm;
use OnPHP\Main\EntityProto\PrototypedBuilder;

abstract class FormBuilder extends PrototypedBuilder
{
	/**
	 * @return Form
	**/
	protected function createEmpty()
	{
		return Form::create();
	}

	/**
	 * @return Form
	**/
	public function fillOwn($object, &$result)
	{
		Assert::isInstance($result, Form::class);

		foreach ($this->getFormMapping() as $primitive) {
			if (
				$primitive instanceof PrimitiveForm
				&& $result->exists($primitive->getName())
				&& $primitive->isComposite()
			) {

				Assert::isEqual(
					$primitive->getProto(),
					$result->get($primitive->getName())->getProto()
				);

				continue;
			}

			$result->add($primitive);
		}

		$result = parent::fillOwn($object, $result);

		$result->setProto($this->proto);

		return $result;
	}
}
?>