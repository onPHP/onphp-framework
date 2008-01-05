<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	final class ExtractPart implements DialectString, MappableObject
	{
		private $what = null;
		private $from = null;
		
		public static function create(
			/* DatePart */ $what,
			/* DialectString */ $from
		)
		{
			return new self($what, $from);
		}
		
		public function __construct(
			/* DatePart */ $what,
			/* DialectString */ $from
		)
		{
			if ($from instanceof DialectString)
				Assert::isTrue(
					($from instanceof DBValue)
					|| ($from instanceof DBField)
				);
			else
				$from = new DBField($from);
			
			if (!$what instanceof DatePart)
				$what = new DatePart($what);
			
			$this->what = $what;
			$this->from = $from;
		}
		
		/**
		 * @return ExtractPart
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return self::create(
				$this->what,
				$dao->guessAtom($this->from, $query)
			);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return
				'EXTRACT('
				.$this->what->toString()
				.' FROM '
				.$this->from->toDialectString($dialect)
				.')';
		}
	}
?>