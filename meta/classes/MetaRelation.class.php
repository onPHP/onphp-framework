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

	final class MetaRelation extends Enumeration
	{
		const ONE_TO_ONE	= 'OneToOne';
		const MANY_TO_ONE	= 'ManyToOne';
		const MANY_TO_MANY	= 'ManyToMany';
		
		protected $names = array(
			self::ONE_TO_ONE		=> 'OneToOne',
			self::MANY_TO_ONE		=> 'ManyToOne',
			self::MANY_TO_MANY		=> 'ManyToMany'
		);
	}
?>