<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	abstract class DimensionStep extends NamedObject
	{
		const FILTER_MASK	= 0x100;
		
		protected $manager	= null;
		
		public function __construct(DimensionStepManager $manager)
		{
			$this->manager = $manager;
		}
		
		public function getManager()
		{
			return $this->manager;
		}
		
		public static function createByType(DimensionStepManager $manager, $type)
		{
			// implement me
		}
	}
?>