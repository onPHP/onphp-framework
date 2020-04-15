<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\EntityProto\Builder;

use OnPHP\Main\EntityProto\EntityProto;
use OnPHP\Main\EntityProto\Accessor\FormImporter;
use OnPHP\Main\EntityProto\Accessor\FormSetter;
use OnPHP\Main\EntityProto\Accessor\ObjectGetter;
use OnPHP\Main\EntityProto\Accessor\ScopeGetter;

final class ScopeToFormImporter extends FormBuilder
{
	/**
	 * @return ScopeToFormImporter
	**/
	public static function create(EntityProto $proto)
	{
		return new self($proto);
	}

	/**
	 * @return ObjectGetter
	**/
	protected function getGetter($object)
	{
		return new ScopeGetter($this->proto, $object);
	}

	/**
	 * @return FormSetter
	**/
	protected function getSetter(&$object)
	{
		return new FormImporter($this->proto, $object);
	}
}
?>