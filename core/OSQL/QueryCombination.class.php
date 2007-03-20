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
	 * @ingroup OSQL
	**/
	final class QueryCombination
		extends QueryIdentification
		implements MappableObject
	{
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		
		public function __construct(
			Query $left, 
			Query $right, 
			$logic
		)
		{
			$this->left		= $left;
			$this->right	= $right;
			$this->logic	= $logic;
		}
		
		public function toMapped(StorableDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->left, $query),
				$dao->guessAtom($this->right, $query),
				$this->logic
			);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return 
				'(' // TODO: parenthesis useless?
				.$this->left->toDialectString($dialect)
				." {$this->logic} "
				.$this->right->toDialectString($dialect)
				.')';
		}
	}
?>