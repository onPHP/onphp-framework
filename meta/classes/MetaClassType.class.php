<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup MetaBase
	**/
	final class MetaClassType extends Enumeration
	{
		const CLASS_FINAL		= 'final';
		const CLASS_ABSTRACT	= 'abstract';
		const CLASS_SPOOKED		= 'spooked';
		
		protected $names = array(
			self::CLASS_FINAL		=> self::CLASS_FINAL,
			self::CLASS_ABSTRACT	=> self::CLASS_ABSTRACT,
			self::CLASS_SPOOKED		=> self::CLASS_SPOOKED
		);
		
		public static function getAnyId()
		{
			return self::CLASS_SPOOKED;
		}
	}
?>