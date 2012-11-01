<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	namespace Onphp;

	interface JoinCapableQuery
	{
		public function from($table, $alias = null);
		public function join($table, LogicalObject $logic, $alias = null);
		public function leftJoin($table, LogicalObject $logic, $alias = null);
		public function rightJoin($table, LogicalObject $logic, $alias = null);
		
		public function hasJoinedTable($table);
	}
?>