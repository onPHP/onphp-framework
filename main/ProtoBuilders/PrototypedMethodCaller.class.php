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
/* $Id$ */

	abstract class PrototypedMethodCaller
	{
		protected $proto = null;
		protected $object = null;
		
		protected $mapping = array();
		
		public function __construct(DTOProto $proto, &$object)
		{
			$this->proto = $proto;
			$this->object = &$object;
			
			$this->mapping = $proto->getFormMapping();
		}
	}
?>