<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton Lebedevich   *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

/*
	$Id$
	
	07 Jun 2005: Separation of {get,set}ters.
	
	28 Mar 2005: Fourth rewrite by Anton.
	
	04 Jan 2005: Third rewrite. Main goal now - simplicity.
*/

	final class SelectQuery extends SelectQuerySkeleton
	{
		public function isDistinct()
		{
			return ($this->distinct === true);
		}

		public function getLimit()
		{
			return $this->limit;
		}
		
		public function getOffset()
		{
			return $this->offset;
		}

		public function getFieldsCount()
		{
			return sizeof($this->fields);
		}
		
		public function toString(Dialect $dialect)
		{
			$fieldList = array();
			foreach ($this->fields as &$field) {
				$fieldList[] = $field->toString($dialect);
			}

			$query = 
				'SELECT '.($this->distinct ? 'DISTINCT ' : '').
				implode(', ', $fieldList);

			$fromString = "";
			for ($i = 0; $i < sizeof($this->from); $i++) {
				if ($i == 0)
					$separator = '';
				elseif (
					$this->from[$i] instanceof FromTable &&
					!$this->from[$i]->getTable() instanceof SelectQuery
				)
					$separator = ', ';
				else
					$separator = ' ';

				$fromString .= $separator.$this->from[$i]->toString($dialect);
			}

			if ($fromString)
				$query .= ' FROM '.$fromString;

			// WHERE
			$query .= parent::toString($dialect);

			/* GROUP */ {
				$groupList = array();

				foreach ($this->group as $group)
					$groupList[] = $group->toString($dialect);

				if (sizeof($groupList))
					$query .= " GROUP BY ".implode(', ', $groupList);
			}

			/* ORDER */ {
				$orderList = array();

				foreach($this->order as $order)
					$orderList[] = $order->toString($dialect);

				if (sizeof($orderList))
					$query .= " ORDER BY ".implode(', ', $orderList);
			}
	
			if ($this->limit)
				$query .= " LIMIT {$this->limit}";
			
			if ($this->offset)
				$query .= " OFFSET {$this->offset}";
	
			return $query;
		}

		protected function getLastTable($table = null)
		{
			if (!$table && sizeof($this->from))
				return $this->from[sizeof($this->from) - 1]->getTable();
			else 
				return $table;
		}
	}
?>