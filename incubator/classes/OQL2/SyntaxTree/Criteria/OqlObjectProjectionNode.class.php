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
	final class OqlObjectProjectionNode extends OqlObjectNode
	{
		protected $class = 'ObjectProjection';
		
		private static $classMap = array(
			'SumProjection'				=> 'sum',
			'AverageNumberProjection'	=> 'avg',
			'MinimalNumberProjection'	=> 'min',
			'MaximalNumberProjection'	=> 'max',
			'RowCountProjection'		=> 'count',
			'DistinctCountProjection'	=> 'count'
		);
		
		private $property	= null;
		private $list		= array();
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public static function create()
		{
			return new self;
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
		
		public function getList()
		{
			return $this->list;
		}
		
		/**
		 * @return OqlObjectProjectionNode
		**/
		public function setList(array $list)
		{
			$this->list = $list;
			
			return $this;
		}
		
		public function toString()
		{
			if ($this->object) {
				$result = '';
				
				if ($this->object instanceof ProjectionChain) {
					if ($this->list) {
						foreach ($this->list as $key => $node) {
							if ($key > 0)
								$result .= ', ';
							
							$result .= $node->toString();
						}
					}
				
				} else {
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
					
					if (
						$this->object instanceof Aliased
						&& $this->object->getAlias()
					)
						$result .= ' as '.$this->object->getAlias();
				}
				
				return $result;
			}
			
			return null;
		}
		
		/**
		 * @return ObjectProjection
		**/
		public function toProjection()
		{
			return $this->toValue();
		}
	}
?>