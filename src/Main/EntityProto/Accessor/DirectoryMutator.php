<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\EntityProto\Accessor;

use OnPHP\Core\Base\Assert;
use OnPHP\Main\EntityProto\EntityProto;
use OnPHP\Main\EntityProto\PrototypedSetter;

abstract class DirectoryMutator extends PrototypedSetter
{
	private $getter = null;

	public function __construct(EntityProto $proto, &$object)
	{
		Assert::isTrue(
			is_dir($object) && is_writable($object),
			'object must be a writeble directory'
		);

		return parent::__construct($proto, $object);
	}

	/**
	 * @return FormGetter
	**/
	public function getGetter()
	{
		if (!$this->getter) {
			$this->getter = new DirectoryGetter($this->proto, $this->object);
		}

		return $this->getter;
	}
}
?>
