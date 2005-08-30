<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov, Anton Lebedevich        *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class DBFunction extends Castable implements DialectString
	{
		private $name = null;
		private $args = array();
		
		public static function create($name)
		{
			if (func_num_args() > 1) {
				$args = func_get_args();
				array_shift($args);
				return new DBFunction($name, $args);
			} else
				return new DBFunction($name);
		}
		
		public function __construct($name)
		{
			$this->name = $name;
			
			if (func_num_args() > 1) {
				$args = func_get_args();
				
				if (is_array($args[1]))
					$this->args = $args[1];
				else {
					array_shift($args);
					$this->args = $args;
				}
			}
		}
		
		public function toString(Dialect $dialect)
		{
			$args = array();

			if ($this->args) {
				foreach ($this->args as &$arg)
					if ($arg instanceof DBValue)
						$args[] = $arg->toString($dialect);
					else
						$args[] = $dialect->fieldToString($arg);
			}
			
			$out =
				$this->name.'('
				.($args == array() ? '' : implode(', ', $args))
				.')';
			
			return
				$this->cast
					? $dialect->toCasted($out, $this->cast)
					: $out;
		}
	}
?>