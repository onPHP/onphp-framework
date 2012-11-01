<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/
	namespace Onphp;

	class ImportCommand extends MakeCommand
	{
		/**
		 * @return \Onphp\ImportCommand
		**/
		public static function create()
		{
			return new self;
		}
		
		protected function daoMethod()
		{
			return 'import';
		}
	}
?>