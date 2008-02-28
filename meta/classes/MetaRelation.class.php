<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup MetaBase
	**/
	final class MetaRelation extends Enumeration
	{
		const ONE_TO_ONE		= 'OneToOne';
		const ONE_TO_MANY		= 'OneToMany';
		const MANY_TO_MANY		= 'ManyToMany';
		
		protected $names = array(
			self::ONE_TO_ONE		=> 'OneToOne',
			self::ONE_TO_MANY		=> 'OneToMany',
			self::MANY_TO_MANY		=> 'ManyToMany'
		);
		
		public static function getAnyId()
		{
			return self::ONE_TO_ONE;
		}
	}
?>