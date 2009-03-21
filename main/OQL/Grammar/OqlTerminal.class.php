<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
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
	class OqlTerminal extends OqlGrammarRule
	{
		protected $type		= null;
		protected $value	= null;
		protected $list		= null;
		
		/**
		 * @return OqlTerminal
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return OqlTerminal
		**/
		public function setType($type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		/**
		 * @return OqlTerminal
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return OqlTerminal
		**/
		public function setList(array $list)
		{
			$this->list = $list;
			
			return $this;
		}
	}
?>