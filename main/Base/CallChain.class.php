<?php
/***************************************************************************
 *   Copyright (C) 2008 by Garmonbozia Research Group                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Helpers
	**/
	final class CallChain
	{
		private $chain = array();
		
		/**
		 * @return CallChain
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return CallChain
		**/
		public function add($object)
		{
			$this->chain[] = $object;
			
			return $this;
		}
		
		public function call($method, $args = null /* , ... */)
		{
			if (!$this->chain)
				throw new WrongStateException();
			
			$args = func_get_args();
			$args = array_shift($args);
			
			if (count($args))
				foreach ($this->chain as $object)
					$result = call_user_func_array(
						array($object, $method),
						$args
					);
			else
				foreach ($this->chain as $object)
					$result = call_user_func(array($object, $method));
			
			return $method;
		}
		
		public function __call($method, $args = null)
		{
			return call_user_func_array(
				array($this, 'call'),
				array_merge(array($method), $args)
			);
		}
	}
?>