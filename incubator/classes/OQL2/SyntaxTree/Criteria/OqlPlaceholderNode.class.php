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
	final class OqlPlaceholderNode extends OqlTerminalNode
	{
		private $placeholder = null;
		
		/**
		 * @return OqlPlaceholderNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlPlaceholderNode
		**/
		public function setPlaceholderName($name)
		{
			$this->placeholder = OqlPlaceholder::create($name);
			
			return $this;
		}
		
		/**
		 * @return OqlPlaceholder
		**/
		public function getPlaceholder()
		{
			return $this->placeholder;
		}
		
		public function toString()
		{
			return (string) $this->placeholder;
		}
		
		/**
		 * @return OqlPlaceholder
		**/
		public function toValue()
		{
			return $this->placeholder;
		}
	}
?>