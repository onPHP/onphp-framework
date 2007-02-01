<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
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
	final class OrderChain implements DialectString
	{
		private $chain = array();
		
		/**
		 * @return OrderChain
		**/
		public function create()
		{
			return new self;
		}
		
		/**
		 * @return OrderChain
		**/
		public function add(OrderBy $order)
		{
			$this->chain[] = $order;
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (!$this->chain)
				return null;
			
			$out = null;
			
			foreach ($this->chain as $order)
				$out .= $order->toDialectString($dialect).', ';
			
			return rtrim($out, ', ');
		}
	}
?>