<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	abstract class SQLChain implements LogicalObject, MappableObject
	{
		protected $chain = array();
		protected $logic = array();
		
		/**
		 * @return SQLChain
		**/
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
		
		/**
		 * @return SQLChain
		**/
		public function toMapped(StorableDAO $dao, JoinCapableQuery $query)
		{
			$size = count($this->chain);
			
			Assert::isTrue($size > 0, 'empty chain');
			
			$chain = new $this;
			
			for ($i = 0; $i < $size; ++$i) {
				$chain->exp(
					$dao->guessAtom($this->chain[$i], $query),
					$this->logic[$i]
				);
			}
			
			return $chain;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if ($this->chain) {
				$out = $this->chain[0]->toDialectString($dialect).' ';
				for ($i = 1, $size = count($this->chain); $i < $size; ++$i) {
					$out .=
						$this->logic[$i]
						.' '
						.$this->chain[$i]->toDialectString($dialect)
						.' ';
				}
				
				if ($size > 1)
					$out = rtrim($out); // trailing space
				
				if ($size === 1)
					return $out;
				
				return '('.$out.')';
			}
			
			return null;
		}
	}
?>