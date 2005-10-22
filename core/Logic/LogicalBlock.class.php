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

	class LogicalBlock implements LogicalObject
	{
		private $args = null;
		private $logic = null;

		public function __construct($args, $logic)
		{
			foreach ($args as &$arg)
				if (!$arg instanceof LogicalObject)
					throw new WrongArgumentException();

			$this->args		= $args;
			$this->logic	= $logic;
		}

		public function toString(Dialect $dialect)
		{
			$quotedArgs = array();
			
			foreach ($this->args as &$arg)
				$quotedArgs[] = $arg->toString($dialect);

			return '('.implode(' '.$this->logic.' ', $quotedArgs).')';
		}
		
		public function toBoolean(Form $form)
		{
			$args	= &$this->args;
			
			switch ($this->logic) {
				case Expression::LOGIC_AND:

					$out = true;
					
					for ($i = 0; $i < sizeof($args); $i++)
						if (isset($args[$i + 1]))
							$out =
								$out
								&& $args[$i]->toBoolean($form)
								&& $args[$i + 1]->toBoolean($form);
						else
							$out = $out && $args[$i]->toBoolean($form);

					return $out;
			
				case Expression::LOGIC_OR:
					
					$out = false;

					for ($i = 0; $i < sizeof($args); $i++)
						if (isset($args[$i + 1]))
							$out =
								$out
								|| $args[$i]->toBoolean($form)
								|| $args[$i + 1]->toBoolean($form);
						else
							$out = $out || $args[$i]->toBoolean($form);
					return $out;

				default:
					throw new WrongStateException();
			}
		}
	}
?>