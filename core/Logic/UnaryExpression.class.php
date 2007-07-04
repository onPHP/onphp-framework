<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Logic
	**/
	abstract class UnaryExpression implements LogicalObject, MappableObject
	{
		protected $subject	= null;
		protected $logic	= null;
		
		public function __construct($subject, $logic)
		{
			$this->subject	= $subject;
			$this->logic	= $logic;
		}
	}
?>