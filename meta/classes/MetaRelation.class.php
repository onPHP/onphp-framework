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
		const ONE_TO_ONE		= 1;
		const ONE_TO_MANY		= 2;
		const MANY_TO_MANY		= 3;
		
		protected $names = array(
			self::ONE_TO_ONE		=> 'OneToOne',
			self::ONE_TO_MANY		=> 'OneToMany',
			self::MANY_TO_MANY		=> 'ManyToMany'
		);
		
		/**
		 * @return MetaRelation
		**/
		public static function create($id)
		{
			return new self($id);
		}
		
		/**
		 * @return MetaRelation
		**/
		public static function makeFromName($name)
		{
			$self = self::create(self::getAnyId());
			$id = array_search($name, $self->getNameList());
			
			if ($id)
				return $self->setId($id);
			
			throw new WrongArgumentException();
		}
	}
?>