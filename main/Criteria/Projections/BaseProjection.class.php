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

	/**
	 * @ingroup Projections
	**/
	abstract class BaseProjection implements ObjectProjection, Aliased
	{
		protected $property	= null;
		protected $alias	= null;
		
		public function __construct($propertyName = null, $alias = null)
		{
			$this->property = $propertyName;
			$this->alias = $alias;
		}

		public function getAlias()
		{
			return $this->alias;
		}

		public function getPropertyName()
		{
			return $this->property;
		}

		public function setPropertyName($propertyName = null)
		{
			$this->property = $propertyName;
		}
	}
?>