<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	**/
	namespace Onphp;

	final class InetType extends IntegerType
	{
		public function getPrimitiveName()
		{
			return 'inet';
		}
		
		public function getSize()
		{
			return null;
		}
		
		/**
		 * @throws \Onphp\WrongArgumentException
		 * @return \Onphp\InetType
		**/
		public function setDefault($default)
		{
			Assert::isTrue(
				long2ip(ip2long($default)) == $default,
				"strange default value given - '{$default}'"
			);
			
			return parent::setDefault($default);
		}
	}
?>