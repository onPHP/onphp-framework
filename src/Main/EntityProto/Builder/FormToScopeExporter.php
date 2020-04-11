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

use OnPHP\Main\EntityProto\Accessor\FormExporter;
use OnPHP\Main\EntityProto\Accessor\FormGetter;
use OnPHP\Main\EntityProto\Accessor\ObjectSetter;
use OnPHP\Main\EntityProto\Accessor\ScopeSetter;
use OnPHP\Main\EntityProto\EntityProto;

final class FormToScopeExporter extends ObjectBuilder
{
	/**
	 * @return FormToObjectConverter
	**/
	public static function create(EntityProto $proto)
	{
		return new self($proto);
	}

	protected function createEmpty()
	{
		return array();
	}

	/**
	 * @return FormGetter
	**/
	protected function getGetter($object)
	{
		return new FormExporter($this->proto, $object);
	}

	/**
	 * @return ObjectSetter
	**/
	protected function getSetter(&$object)
	{
		return new ScopeSetter($this->proto, $object);
	}
}
?>