<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Base;

use OnPHP\Core\Form\Form;
use OnPHP\Core\OSQL\InsertOrUpdateQuery;
use OnPHP\Core\Base\Prototyped;
use OnPHP\Main\DAO\ProtoDAO;

/**
 * @see LightMetaProperty
 * 
 * @ingroup Helpers
**/
final class InnerMetaProperty extends LightMetaProperty
{
	/**
	 * @return InnerMetaProperty
	**/
	public static function create()
	{
		return new self;
	}

	public function isBuildable($array, $prefix = null)
	{
		return true;
	}

	public function fillMapping(array $mapping)
	{
		return
			array_merge(
				$mapping,
				$this->getProto()->getMapping()
			);
	}

	/**
	 * @return Form
	**/
	public function fillForm(Form $form, $prefix = null)
	{
		foreach ($this->getProto()->getPropertyList() as $property) {
			$property->fillForm($form, $this->getName().':');
		}

		return $form;
	}

	public function fillQuery(
		InsertOrUpdateQuery $query,
		Prototyped $object,
		Prototyped $old = null
	)
	{
		$inner = $object->{$this->getGetter()}();
		$oldInner = $old ? $old->{$this->getGetter()}() : null;

		return
			$this->getProto()->fillQuery(
				$query,
				$inner,
				//when old and objects have one value object
				//  we'll update all valueObject fields:
				$oldInner !== $inner ? $oldInner : null
			);
	}

	public function toValue(ProtoDAO $dao = null, $array, $prefix = null)
	{
		$proto = $this->getProto();

		return $proto->completeObject(
			$proto->makeOnlyObject(
				$this->getClassName(), $array, $prefix, $dao
			),
			$array,
			$prefix
		);
	}

	/**
	 * @return AbstractProtoClass
	**/
	public function getProto()
	{
		return call_user_func(array($this->getClassName(), 'proto'));
	}

	public function isFormless()
	{
		return true;
	}
}
?>
