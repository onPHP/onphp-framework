<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Helpers
	**/
	namespace Onphp;

	final class Hstore implements Stringable
	{
		protected $properties = array();
		
		/**
		 * Create Hstore by raw string.
		 *
		 * @return \Onphp\Hstore
		**/
		public static function create($string)
		{
			$self = new self();
			
			return $self->toValue($string);
		}
		
		/**
		 * Create Hstore by array.
		 *
		 * @return \Onphp\Hstore
		**/
		public static function make($array)
		{
			$self = new self();
			
			return $self->setList($array);
		}
		
		/**
		 * @return \Onphp\Hstore
		**/
		public function setList($array)
		{
			$this->properties = $array;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->properties;
		}
		
		public function get($key)
		{
			if (!$this->isExists($key))
				throw new ObjectNotFoundException("Property with name '{$key}' does not exists");
			
			return $this->properties[$key];
		}
		
		/**
		 * @return \Onphp\Hstore
		**/
		public function set($key, $value)
		{
			$this->properties[$key] = $value;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\Hstore
		**/
		public function drop($key)
		{
			unset($this->properties[$key]);
			
			return $this;
		}
		
		public function isExists($key)
		{
			return key_exists($key, $this->properties);
		}
		
		/**
		 * @return \Onphp\Hstore
		**/
		public function toValue($raw)
		{
			if (!$raw)
				return $this;
			
			$this->properties = $this->parseString($raw);

			return $this;
		}

		public function toString()
		{
			if (empty($this->properties))
				return null;

			$string = '';
			
			foreach ($this->properties as $k => $v) {
				if ($v !== null)
					$string .= "\"{$this->quoteValue($k)}\"=>\"{$this->quoteValue($v)}\",";
				else
					$string .= "\"{$this->quoteValue($k)}\"=>NULL,";
			}
			
			return $string;
		}
		
		protected function quoteValue($value)
		{
			return addslashes($value);
		}
		
		private function parseString($raw)
		{
			$raw = preg_replace('/([$])/u', "\\\\$1", $raw);
			$unescapedHStore = array();
			eval('$unescapedHStore = array(' . $raw . ');');
			return $unescapedHStore;
		}
	}
?>