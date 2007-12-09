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
/* $Id$ */

	/**
	 * Transaction-like wrapper around DB's queryNull.
	 * 
	 * @ingroup Transaction
	**/
	final class FakeTransaction extends BaseTransaction
	{
		/**
		 * @return FakeTransaction
		**/
		public function add(Query $query)
		{
			$this->db->queryNull($query);
			
			return $this;
		}
		
		/**
		 * @return FakeTransaction
		**/
		public function flush()
		{
			return $this;
		}
	}
?>