<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Anton E. Lebedevich                        *
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
	abstract class SQLBaseJoin implements SQLTableName, DialectString
	{
		protected $subject	= null;
		protected $alias	= null;
		protected $logic	= null;

		public function __construct($subject, LogicalObject $logic, $alias)
		{
			$this->subject	= $subject;
			$this->alias	= $alias;
			$this->logic	= $logic;
		}

		public function getTable()
		{
			return $this->alias ? $this->alias : $this->subject;
		}
		
		protected function baseToString(Dialect $dialect, $logic = null)
		{
			return
				$logic.'JOIN '
					.($this->subject instanceof DialectString
						?
							$this->subject instanceof SelectQuery
								? '('.$this->subject->toDialectString($dialect).')'
								: $this->subject->toDialectString($dialect)
						: $dialect->quoteTable($this->subject)
					)
				.($this->alias ? ' AS '.$dialect->quoteTable($this->alias) : null)
				.' ON '.$this->logic->toDialectString($dialect);
		}
	}
?>