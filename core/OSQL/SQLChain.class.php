<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich*
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	abstract class SQLChain implements DialectString
	{
		protected $chain = array();
		protected $logic = array();
		
		protected function exp(DialectString $exp, $logic)
		{
			$this->chain[] = $exp;
			$this->logic[] = $logic;
			
			return $this;
		}
		
		public function getSize()
		{
			return count($this->chain);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if ($this->chain) {
				$out = "({$this->chain[0]->toDialectString($dialect)} ";
	
				for ($i = 1, $size = count($this->chain); $i < $size; ++$i)
					$out .=
						$this->logic[$i]
						.' '
						.$this->chain[$i]->toDialectString($dialect)
						.' ';

				return rtrim($out).')'; // trailing space, if any
			}
			
			return null;
		}
	}
?>