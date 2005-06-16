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

	class EqLowerExpression implements LogicalObject
	{
		private $field = null;
		private $value = null;

		public function __construct($field, $value)
		{
			$this->field = $field;
			$this->value = $value;
		}

		public function toString(DB $db)
		{
			return
				"(lower({$db->fieldToString($this->field)}) = ".
				"lower({$db->quoteValue($this->value)}))";
		}
		
		public function toBoolean(Form $form)
		{
			try {
				$left = &$form->getValue($this->left);
			} catch (ObjectNotFoundException $e) {
				$left = &$this->left;
			}

			try {
				$right = &$form->getValue($this->right);
			} catch (ObjectNotFoundException $e) {
				$right = &$this->right;
			}
			
			return
				strtolower($left) === strtolower($right);
		}
	}
?>
