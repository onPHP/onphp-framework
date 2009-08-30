<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	abstract class DimensionStep extends NamedObject
	{
		const FILTER_MASK	= 0x100;
		
		protected $manager	= null;
		
		public function __construct(DimensionStepManager $manager)
		{
			$this->manager = $manager;
		}
		
		/**
		 * @return DimensionStepManager
		**/
		public function getManager()
		{
			return $this->manager;
		}
		
		/**
		 * @return DimensionStep
		**/
		public static function createByType(DimensionStepManager $manager, $type)
		{
			throw new UnimplementedFeatureException('implement me');
		}
	}
?>