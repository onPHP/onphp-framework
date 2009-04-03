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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OqlToken
	{
		private $value;
		private $rawValue;
		private $type;
		
		/**
		 * @return OqlToken
		**/
		public static function create($value, $rawValue, $type)
		{
			return new self($value, $rawValue, $type);
		}
		
		public function __construct($value, $rawValue, $type)
		{
			$this->value = $value;
			$this->rawValue = $rawValue;
			$this->type = $type;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function getRawValue()
		{
			return $this->rawValue;
		}
		
		public function getType()
		{
			return $this->type;
		}
	}
?>