<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton Lebedevich   *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class BetweenExpression implements LogicalObject
	{
		private $field  = null;
		private $left   = null;
		private $right  = null;
		
		public function __construct($field, $left, $right)
		{
			$this->left		= $left;
			$this->right	= $right;
			$this->field	= $field;
		}
		
		private function fieldOrValue(DB $db, $something)
		{
			if ($something instanceof DBField || $something instanceof DBValue)
				return $something->toString($db);
			else
				return $db->quoteField($something);
		}
		
		public function toString(DB $db)
		{
			return
				'('.
					$this->fieldOrValue($db, $this->field).
					' BETWEEN '.
						$this->fieldOrValue($db, $this->left).
					' AND '.
						$this->fieldOrValue($db, $this->right).
				')';
		}
		
		public function toBoolean(Form $form)
		{
			$value = &$form->getValue($this->field);
			
			return
				($form->getValue($this->left) <= $value) &&
				($value >= $form->getValue($this->right));
		}
	}
?>