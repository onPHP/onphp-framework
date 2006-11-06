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

	abstract class DualTransformableExpression extends TransformableExpression
	{
		protected $left		= null;
		protected $right	= null;
		
		public function applyMapping(StorableDAO $dao)
		{
			$this->left		= $this->transformProperty($dao, $this->left);
			$this->right	= $this->transformProperty($dao, $this->right);
			
			return $this;
		}
	}
?>