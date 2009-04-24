<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlToken
	{
		const DELIMITER = '::';
		
		private static $pool = array();
		
		private $type;
		private $value;
		
		/**
		 * @return OqlToken
		**/
		public static function create($type, $value)
		{
			$key = $type.self::DELIMITER.$value;
			if (!isset(self::$pool[$key]))
				self::$pool[$key] = new self($type, $value);
			
			return self::$pool[$key];
		}
		
		public function __construct($type, $value)
		{
			$this->type = $type;
			$this->value = $value;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>