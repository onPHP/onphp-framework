<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlOrderByClause extends OqlQueryListedClause
	{
		/**
		 * @return OqlOrderByClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OrderChain
		**/
		public function toOrder()
		{
			$order = OrderChain::create();
			foreach ($this->list as $property) {
				$order->add(
					$property->evaluate($this->parameters)
				);
			}
			
			return $order;
		}
	}
?>