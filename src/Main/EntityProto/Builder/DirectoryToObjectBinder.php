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

namespace OnPHP\Main\EntityProto\Builder;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Exception\WrongStateException;
use OnPHP\Main\EntityProto\Accessor\DirectoryGetter;
use OnPHP\Main\EntityProto\Accessor\FormGetter;
use OnPHP\Main\EntityProto\Accessor\ObjectSetter;
use OnPHP\Main\EntityProto\DirectoryContext;
use OnPHP\Main\EntityProto\EntityProto;
use OnPHP\Main\EntityProto\PrototypedBuilder;
use OnPHP\Main\Net\GenericUri;

final class DirectoryToObjectBinder extends ObjectBuilder
{
	private $identityMap = null;

	/**
	 * @return FormToObjectConverter
	**/
	public static function create(EntityProto $proto)
	{
		return new self($proto);
	}

	public function __construct(EntityProto $proto)
	{
		parent::__construct($proto);

		$this->identityMap = new DirectoryContext;
	}

	public function setIdentityMap(DirectoryContext $identityMap)
	{
		$this->identityMap = $identityMap;

		return $this;
	}

	/**
	 * @return DirectoryContext
	**/
	public function getIdentityMap()
	{
		return $this->identityMap;
	}

	/**
	 * @return PrototypedBuilder
	**/
	public function cloneBuilder(EntityProto $proto)
	{
		$result = parent::cloneBuilder($proto);

		$result->setIdentityMap($this->identityMap);

		return $result;
	}

	public function cloneInnerBuilder($property)
	{
		$result = parent::cloneInnerBuilder($property);

		$result->setIdentityMap($this->identityMap);

		return $result;
	}

	/**
	 * @return PrototypedBuilder
	**/
	public function makeReverseBuilder()
	{
		return
			ObjectToDirectoryBinder::create($this->proto)->
			setIdentityMap($this->identityMap);
	}

	public function make($object, $recursive = true)
	{
		Assert::isTrue(is_readable($object), "required object `$object` must exist");

		$realObject = $this->getRealObject($object);

		$result = $this->identityMap->lookup($realObject);

		if ($result)
			return $result;

		$result = parent::make($realObject, $recursive);

		if ($result instanceof Identifiable)
			$result->setId(basename($realObject));

		return $result;
	}

	protected function initialize($object, &$result)
	{
		parent::initialize($object, $result);

		$realObject = $this->getRealObject($object);

		$this->identityMap->bind($realObject, $result);

		return $this;
	}

	/**
	 * @return FormGetter
	**/
	protected function getGetter($object)
	{
		return new DirectoryGetter($this->proto, $object);
	}

	/**
	 * @return ObjectSetter
	**/
	protected function getSetter(&$object)
	{
		return new ObjectSetter($this->proto, $object);
	}

	private function getRealObject($object)
	{
		$result = $object;

		if (is_link($object)) {
			$result = readlink($object);

			if ($result === false)
				throw new WrongStateException("invalid link: $object");

			if (substr($result, 0, 1) !== DIRECTORY_SEPARATOR) {
				$result = GenericUri::create()->
					setScheme('file')->
					setPath($object)->
						transform(
							GenericUri::create()->
							setPath($result)
						)->
							getPath();
			}
		}

		$realResult = realpath($result);

		if ($realResult === false)
			throw new WrongStateException(
				"invalid context: $object ($result)"
			);

		return $realResult;
	}
}
?>
