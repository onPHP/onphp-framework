<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Name says it all. :-)
	 * 
	 * @ingroup Logic
	**/
	final class InExpression implements LogicalObject, MappableObject
	{
		const IN		= 'in';
		const NOT_IN	= 'not in';
		
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		
		public function __construct($left, $right, $logic)
		{
			Assert::isTrue(
				($right instanceof SelectQuery)
				|| ($right instanceof Criteria)
				|| is_array($right)
			);
			
			$this->left		= $left;
			$this->right	= $right;
			$this->logic	= $logic;
		}
		
		/**
		 * @return InExpression
		**/
		public function toMapped(StorableDAO $dao, JoinCapableQuery $query)
		{
			if (is_array($this->right)) {
				$right = array();
				foreach ($this->right as $atom) {
					$right[] = $dao->guessAtom($atom, $query);
				}
			} else
				$right = $this->right; // untransformable
			
			return new self(
				$dao->guessAtom($this->left, $query),
				$right,
				$this->logic
			);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$string = 
				'('
				.$dialect->toFieldString($this->left)
				.' '.$this->logic
				.' ';
			
			$right = $this->right;
			
			if (
				($right instanceof SelectQuery)
				|| ($right instanceof Criteria)
			) {
			
				$string .= '('.$right->toDialectString($dialect).')';
				
			} elseif (is_array($right)) {
				
				$string .= SQLArray::create($right)->
					toDialectString($dialect);
					
			} else
				throw new WrongArgumentException(
					'sql select or array accepted by '.$this->logic
				);

			$string .= ')';

			return $string;
		}
		
		public function toBoolean(Form $form)
		{
			$left	= $form->toFormValue($this->left);
			$right	= $this->right;
			
			$both = 
				(null !== $left)
				&& (null !== $right);

			switch ($this->logic) {
				
				case self::IN:
					return $both && (in_array($left, $right));
				
				case self::NOT_IN:
					return $both && (!in_array($left, $right));
				
				default:
					
					throw new UnsupportedMethodException(
						"'{$this->logic}' doesn't supported"
					);
			}
		}
	}
?>