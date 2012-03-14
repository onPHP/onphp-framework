<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveUuid extends PrimitiveString
	{
		const UUID_PATTERN = '/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i';

		/**
		 * @param string $name
		 * @return PrimitiveUuid
		 */
		public static function create($name)
		{
			return new self($name);
		}

		public function __construct($name)
		{
			parent::__construct($name);
			parent::setAllowedPattern(self::UUID_PATTERN);
		}

		/**
		 * @param $pattern
		 * @throws UnsupportedMethodException
		 */
		public function setAllowedPattern($pattern)
		{
			throw new UnsupportedMethodException('this method not supported yet!');
		}

	}