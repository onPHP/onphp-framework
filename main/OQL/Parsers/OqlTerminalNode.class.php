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
	class OqlTerminalNode extends OqlSyntaxNode
	{
		protected $token = null;
		
		/**
		 * @return OqlTerminalNode
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
		 * @return OqlSyntaxNode
		**/
		public function setToken(OqlToken $token)
		{
			$this->token = $token;
			
			return $this;
		}
	}
?>