<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 27.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

class PrimitiveUuidString extends PrimitiveString {

	const UUID_PATTERN = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';

	/**
	 * @param string $name
	 * @return PrimitiveUuidString
	 */
	public static function create($name)
	{
		return new self($name);
	}

	public function __construct($name)
	{
		parent::__construct($name);
		$this->setAllowedPattern(self::UUID_PATTERN);
	}

}
