<?php
/****************************************************************************
 *   Copyright (C) 2012 by Nikita V. Konstantinov                           *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup Logic
	**/
	final class DistanceBetweenPointsExpression implements LogicalObject, MappableObject
	{
		/**
		 * @var Point|string 
		**/
		private $p1 = null;

		/**
		 * @var Point|string 
		**/		
		private $p2 = null;
		
		public function __construct($p1, $p2)
		{
			$this->p1 = $p1;
			$this->p2 = $p2;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteDistanceBetweenPoints($this->p1, $this->p2);
		}
		
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->p1, $query),
				$dao->guessAtom($this->p2, $query)
			);
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException('It makes no sense');
		}
	}
?>