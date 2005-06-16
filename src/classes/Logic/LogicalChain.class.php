<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class LogicalChain implements LogicalObject
	{
		private $chain = array();
		private $logic = array();
		
		public function expAnd(LogicalObject $exp)
		{
			return $this->exp($exp, Expression::LOGIC_AND);
		}
		
		public function expOr(LogicalObject $exp)
		{
			return $this->exp($exp, Expression::LOGIC_OR);
		}
		
		private function exp(LogicalObject $exp, $logic)
		{
			$this->chain[] = $exp;
			$this->logic[] = $logic;
			
			return $this;
		}
		
		public function getSize()
		{
			return sizeof($this->chain);
		}
		
		public function toString(DB $db)
		{
			if ($this->chain) {
				$out = '(';
				$this->logic[0] = '';
	
				for ($i = 0; $i < sizeof($this->chain); $i++)
					$out .= "{$this->logic[$i]} {$this->chain[$i]->toString($db)} ";
	
				return $out.')';
			}
			
			return null;
		}
		
		public function toBoolean(Form $form)
		{
			$chain = &$this->chain;
			
			$out = null;
			
			for ($i = 0; $i < sizeof($chain); $i++) {
				if (isset($chain[$i + 1]))
					$out =
						Expression::toBoolean(
							$this->logic[$i + 1],
							$chain[$i]->toBoolean($form),
							$chain[$i + 1]->toBoolean($form)
						);
				else
					$out =
						Expression::toBoolean(
							$this->logic[$i],
							$out,
							$chain[$i]
						);
			}

			return $out;
		}
	}
?>