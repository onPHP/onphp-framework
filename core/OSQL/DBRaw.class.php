<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Karma's destroyer.
	 * 
	 * @deprecated since the begining of time
	 * 
	 * @ingroup OSQL
	**/
	namespace Onphp;

	final class DBRaw implements LogicalObject
	{
		private $string = null;
		
		public function __construct($rawString)
		{
			if (!defined('__I_HATE_MY_KARMA__'))
				throw new UnsupportedMethodException(
					'do not use it. please.'
				);
			
			$this->string = $rawString;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $this->string;
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException();
		}
	}
?>