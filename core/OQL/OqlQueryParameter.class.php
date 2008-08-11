<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	class OqlQueryParameter
	{
		private $value		= null;
		private $bindable	= false;
		
		/**
		 * @return OqlQueryParameter
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function isBindable()
		{
			return $this->bindable;
		}
		
		/**
		 * @return OqlQueryParameter
		**/
		public function setBindable($bindable)
		{
			$this->bindable = $bindable;
			
			return $this;
		}
		
		public function evaluate($values)
		{
			if ($this->isBindable()) {
				Assert::isPositiveInteger(
					$this->getValue(),
					'wrong substitution number: $'.$this->getValue()
				);
				Assert::isIndexExists(
					$values,
					$this->getValue(),
					'parameter $'.$this->getValue().' is not binded'
				);
				
				$value = $values[$this->getValue()];
				
			} else
				$value = $this->getValue();
			
			if ($value instanceof Identifiable)
				return $value->getId();
				
			elseif (is_array($value)) {
				$list = array();
				foreach ($value as $key => $parameter)
					$list[$key] = $parameter->evaluate($values);
				
				return $list;
			}
			
			return $value;
		}
	}
?>