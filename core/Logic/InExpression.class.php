<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Name says it all. :-)
	 * 
	 * @ingroup Logic
	**/
	namespace Onphp;

	final class InExpression implements LogicalObject, MappableObject
	{
		const IN		= 'IN';
		const NOT_IN	= 'NOT IN';
		
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		
		public function __construct($left, $right, $logic)
		{
			Assert::isTrue(
				($right instanceof Query)
				|| ($right instanceof Criteria)
				|| ($right instanceof MappableObject)
				|| is_array($right)
			);
			
			Assert::isTrue(
				($logic == self::IN)
				|| ($logic == self::NOT_IN)
			);
			
			$this->left		= $left;
			$this->right	= $right;
			$this->logic	= $logic;
		}

		public function getLeft()
		{
			return $this->left;
		}

		public function getRight()
		{
			return $this->right;
		}

		public function getLogic()
		{
			return $this->logic;
		}
		
		/**
		 * @return \Onphp\InExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			if (is_array($this->right)) {
				$right = array();
				foreach ($this->right as $atom) {
					$right[] = $dao->guessAtom($atom, $query);
				}
			} elseif ($this->right instanceof MappableObject)
				$right = $this->right->toMapped($dao, $query);
			else
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
			
			if ($right instanceof DialectString) {
			
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