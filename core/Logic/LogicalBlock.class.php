<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Wrapper around given childs of LogicalObject with single logic-glue.
	 * 
	 * @ingroup Logic
	**/
	final class LogicalBlock implements LogicalObject
	{
		private $args = null;
		private $logic = null;

		public function __construct($args, $logic)
		{
			foreach ($args as &$arg) {
				if (
					!$arg instanceof LogicalObject
					&& !$arg instanceof SelectQuery
				)
					throw new WrongArgumentException(
						'unsupported object type: '.get_class($arg)
					);
			}
			
			Assert::isTrue(
				($logic == Expression::LOGIC_AND)
				|| ($logic == Expression::LOGIC_OR),
				
				"unknown logic '{$logic}'"
			);
			
			$this->args		= $args;
			$this->logic	= $logic;
		}

		public function toDialectString(Dialect $dialect)
		{
			$quotedArgs = array();
			
			foreach ($this->args as &$arg)
				$quotedArgs[] = $arg->toDialectString($dialect);

			return '('.implode(' '.$this->logic.' ', $quotedArgs).')';
		}
		
		public function toBoolean(Form $form)
		{
			$args = &$this->args;
			$size = count($args);
			
			switch ($this->logic) {
				case Expression::LOGIC_AND:
					
					$out = true;
					for ($i = 0; $i < $size; ++$i)
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
					for ($i = 0; $i < $size; ++$i)
						if (isset($args[$i + 1]))
							$out =
								$out
								|| $args[$i]->toBoolean($form)
								|| $args[$i + 1]->toBoolean($form);
						else
							$out = $out || $args[$i]->toBoolean($form);
					
					return $out;

				default:
					throw new WrongStateException(
						'the thing that should not be, indeed'
					);
			}
		}
	}
?>