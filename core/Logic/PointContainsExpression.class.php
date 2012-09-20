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
	final class PointContainsExpression implements LogicalObject, MappableObject
	{
		private $polygon = null;
		private $point = null;
		
		public function __construct($polygon, $point)
		{
			$this->polygon = $polygon;
			$this->point = $point;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quotePointInPolygon($this->polygon, $this->point);
		}
		
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->polygon, $query),
				$dao->guessAtom($this->point, $query)
			);
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException('Author was lazy');
		}
	}
?>