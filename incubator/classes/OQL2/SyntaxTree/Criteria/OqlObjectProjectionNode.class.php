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
	final class OqlObjectProjectionNode extends OqlTerminalNode
	{
		private static $classMap = array(
			'SumProjection'	=> 'sum',
			'AverageNumberProjection'	=> 'avg',
			'MinimalNumberProjection'	=> 'min',
			'MaximalNumberProjection'	=> 'max',
			'RowCountProjection'		=> 'count',
			'DistinctCountProjection'	=> 'count'
		);
		
		private $object		= null;
		private $property	= null;
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function getObject()
		{
			return $this->object;
		}
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public function setObject(ObjectProjection $object)
		{
			$this->object = $object;
			
			return $this;
		}
		
		public function getProperty()
		{
			return $this->property;
		}
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public function setProperty($property)
		{
			$this->property = $property;
			
			return $this;
		}
		
		public function toString()
		{
			if ($this->object) {
				$result = '';
				
				$isAggregate = isset(self::$classMap[get_class($this->object)]);
				if ($isAggregate) {
					$result .= self::$classMap[get_class($this->object)].'(';
					if ($this->object instanceof DistinctCountProjection)
						$result .= 'distinct ';
				}
				
				if ($this->property instanceof DialectString)
					$result .= $this->property->toDialectString(ImaginaryDialect::me());
				else
					$result .= $this->property;
				
				if ($isAggregate)
					$result .= ')';
				
				if ($this->object instanceof Aliased && $this->object->getAlias())
					$result .= ' as '.$this->object->getAlias();
				
				return $result;
			}
			
			return null;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function toValue()
		{
			return $this->object;
		}
	}
?>