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
	namespace Onphp;

	final class CallChain
	{
		private $chain = array();
		
		/**
		 * @return \Onphp\CallChain
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\CallChain
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
			array_shift($args);
			
			if (count($args)) {
				$result = $args;
				foreach ($this->chain as $object)
					$result = call_user_func_array(
						array($object, $method),
						is_array($result)
							? $result
							: array($result)
					);
			} else {
				foreach ($this->chain as $object)
					$result = call_user_func(array($object, $method));
			}
			
			return $result;
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