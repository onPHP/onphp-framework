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
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OqlSyntaxErrorException extends BaseException
	{
		private $tokenIndex = null;
		
		public function __construct($message, $tokenIndex = null, $code = null)
		{
			parent::__construct($message, $code);
			
			$this->tokenIndex = $tokenIndex;
		}
		
		public function getTokenIndex()
		{
			return $this->tokenIndex;
		}
	}
?>