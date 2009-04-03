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
	final class OqlTokenNode extends OqlTerminalNode 
	{
		protected $token = null;
		
		/**
		 * @return OqlTokenNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlToken
		**/
		public function getToken()
		{
			return $this->token;
		}
		
		/**
		 * @return OqlTokenNode
		**/
		public function setToken(OqlToken $token)
		{
			$this->token = $token;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->token ? $this->token->getValue() : null;
		}
		
		public function getType()
		{
			return $this->token ? $this->token->getType() : null;
		}
		
		public function toString()
		{
			return $this->getValue();
		}
	}
?>