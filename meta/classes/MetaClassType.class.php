<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup MetaBase
	**/
	final class MetaClassType extends Enumeration
	{
		const CLASS_FINAL		= 'final';
		const CLASS_ABSTRACT	= 'abstract';
		
		protected $names = array(
			self::CLASS_FINAL		=> self::CLASS_FINAL,
			self::CLASS_ABSTRACT	=> self::CLASS_ABSTRACT
		);
	}
?>