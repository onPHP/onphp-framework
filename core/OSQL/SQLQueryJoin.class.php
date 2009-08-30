<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	class SQLQueryJoin extends SQLBaseJoin implements SQLTableName
	{
		public function __construct(
			SelectQuery $query, LogicalObject $logic, $alias
		)
		{
			parent::__construct($query, $logic, $alias);
		}

		public function toString(Dialect $dialect)
		{
			return
				'JOIN ('.$this->subject->toString($dialect).') AS '.
				$dialect->quoteTable($this->alias).
				' ON '.$this->logic->toString($dialect);
		}

		public function getTable()
		{
			return $this->alias;
		}
	}
?>