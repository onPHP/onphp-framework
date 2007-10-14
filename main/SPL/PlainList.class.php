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
	 * Ordered unindexed list of Identifiable objects.
	 * 
	 * @ingroup onSPL
	**/
	final class PlainList extends AbstractList
	{
		public static function create()
		{
			return new self;
		}
		
		public function offsetSet($offset, $value)
		{
			Assert::isTrue($value instanceof Identifiable);
			
			$this->list[] = $value;
			
			return $this;
		}
	}
?>