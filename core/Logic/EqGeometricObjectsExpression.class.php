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
	final class EqGeometricObjectsExpression implements LogicalObject, MappableObject
	{
		private $left  = null;
		private $right = null;
		private $type  = null;
		
		public function __construct($left, $right, $type)
		{
			Assert::isTrue(
				in_array($type, array(DataType::POINT, DataType::POLYGON)),
				'Unknown type of geometric object'
			);			
			
			$this->left  = $left;
			$this->right = $right;
			$this->type  = $type;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			switch ($this->type) {
				case DataType::POINT:
					return
						$dialect->
							quoteEqPoints($this->left, $this->right);
					
				case DataType::POLYGON:
					return
						$dialect->
							quoteEqPolygons($this->left, $this->right);
					
				default:
					throw new WrongStateException('Unknown type');
			}
		}
		
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->left, $query),
				$dao->guessAtom($this->right, $query),
				$this->type
			);
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException('Author was lazy');
		}
	}
?>