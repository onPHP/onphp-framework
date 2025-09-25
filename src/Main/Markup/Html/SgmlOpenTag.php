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

namespace OnPHP\Main\Markup\Html;

use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @ingroup Html
**/
final class SgmlOpenTag extends SgmlTag
{
	/**
	 * @var array
	 */
	private array $attributes = [];
	/**
	 * @var bool
	 */
	private bool $empty = false;

	/**
	 * @param bool $isEmpty
	 * @return static
	 */
	public function setEmpty(bool $isEmpty): SgmlOpenTag
	{
		$this->empty = $isEmpty;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->empty;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function setAttribute(string $name, $value = null): SgmlOpenTag
	{
		if ($this->hasAttribute($name)) {
			throw new WrongArgumentException("attribute '{$name}' already exist");
		}

		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasAttribute(string $name): bool
	{
		return in_array(
			mb_strtolower($name),
			array_map('mb_strtolower', array_keys($this->attributes))
		);
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @throws WrongArgumentException
	 */
	public function getAttribute(string $name)
	{
		$name = mb_strtolower($name);

		foreach($this->attributes as $attributeName => $value) {
			if (mb_strtolower($attributeName) == $name) {
				return $value;
			}
		}

		throw new WrongArgumentException("attribute '{$name}' does not exist");
	}

	/**
	 * @param string $name
	 * @return static
	 * @throws WrongArgumentException
	 */
	public function dropAttribute(string $name): SgmlOpenTag
	{
		$name = mb_strtolower($name);

		foreach($this->attributes as $attributeName => $value) {
			if (mb_strtolower($attributeName) == $name) {
				unset($this->attributes[$attributeName]);
				return $this;
			}
		}

		throw new WrongArgumentException("attribute '{$name}' does not exist");
	}

	/**
	 * @return array
	 */
	public function getAttributesList(): array
	{
		return $this->attributes;
	}

	/**
	 * @return static
	 */
	public function dropAttributesList(): SgmlOpenTag
	{
		$this->attributes = array();

		return $this;
	}
}