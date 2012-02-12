<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Reference for calling built-in database functions.
	 * 
	 * @ingroup OSQL
	**/
	final class SQLFunction extends Castable implements MappableObject, Aliased
	{
		const AGGREGATE_ALL			= 1;
		const AGGREGATE_DISTINCT	= 2;
		
		private $name		= null;
		private $alias		= null;
		private $aggregate	= null;
		
		private $args	= array();
		private $joiners = array();
		
		/**
		 * @return SQLFunction
		**/
		public static function create($name /* , ... */)
		{
			if (func_num_args() > 1) {
				$args = func_get_args();
				array_shift($args);
				return new SQLFunction($name, $args);
			} else
				return new SQLFunction($name);
		}
		
		public function __construct($name /* , ... */)
		{
			$this->name = $name;
			
			if (func_num_args() > 1) {
				$args = func_get_args();
				
				if (is_array($args[1]))
					$this->args = $args[1];
				else {
					array_shift($args);
					$this->args = $args;
				}
			}
		}
		
		public function getAlias()
		{
			return $this->alias;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return SQLFunction
		**/
		public function setAlias($alias)
		{
			$this->alias = $alias;
			
			return $this;
		}
		
		/**
		 * @return SQLFunction
		**/
		public function setAggregateAll()
		{
			$this->aggregate = self::AGGREGATE_ALL;
			
			return $this;
		}
		
		/**
		 * @return SQLFunction
		**/
		public function setAggregateDistinct()
		{
			$this->aggregate = self::AGGREGATE_DISTINCT;
			
			return $this;
		}
		
		/**
		 * @return SQLFunction
		**/
		public function setJoiner($joiner, $index = 0)
		{
			$this->joiners[$index] = $joiner;
			
			return $this;
		}
		
		public function getJoiner($index)
		{
			if (array_key_exists($index, $this->joiners)) {
				return $this->joiners[$index];
			} else {
				return ', ';
			}
		}

		/**
		 * @return SQLFunction
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$mapped = array();
			
			$mapped[] = $this->name;
			
			foreach ($this->args as $arg) {
				if ($arg instanceof MappableObject)
					$mapped[] = $arg->toMapped($dao, $query);
				else
					$mapped[] = $dao->guessAtom($arg, $query);
			}
			
			$sqlFunction = call_user_func_array(array('self', 'create'), $mapped);
			/* @var $sqlFunction SQLFunction */
			
			$sqlFunction->aggregate = $this->aggregate;
			
			$sqlFunction->castTo($this->cast);
			foreach ($this->joiners as $index => $joiner) {
				$sqlFunction->setJoiner($joiner, $index);
			}
			
			return $sqlFunction;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$args = array();
			
			if ($this->args) {
				foreach ($this->args as $arg)
					if ($arg instanceof DBValue)
						$args[] = $arg->toDialectString($dialect);
					// we're not using * anywhere but COUNT()
					elseif ($arg === '*') {
						Assert::isTrue(
							(strtolower($this->name) === 'count')
							|| defined('__I_HATE_MY_KARMA__'),
							
							'do not want to use "*" with '.$this->args[0]
						);
						
						$args[] = $dialect->quoteValue($arg);
					} elseif ($arg instanceof SelectQuery)
						$args[] = '('.$dialect->fieldToString($arg).')';
					else
						$args[] = $dialect->fieldToString($arg);
			}
			
			$out = $this->name.'(';
			
			if ($this->aggregate == self::AGGREGATE_ALL) {
				$out .= 'ALL ';
			} elseif ($this->aggregate == self::AGGREGATE_DISTINCT) {
				$out .= 'DISTINCT ';
			}
			
			$out .= ($args == array() ? null : $this->joinArgs($args)).')';
			
			$out =
				$this->cast
					? $dialect->toCasted($out, $this->cast)
					: $out;
			
			return
				$this->alias
					? $out.' AS '.$dialect->quoteTable($this->alias)
					: $out;
		}
		
		private function joinArgs(array $args) {
			if (count($args) == 0) {
				return '';
			} elseif (count($this->joiners) == 0) {
				return implode(', ', $args);
			} else {
				$joinedArgs = '';
				for ($i = 0; $i < count($args); $i++) {
					$joinedArgs .= $args[$i];
					if ($i < count($args) - 1) {
						$joinedArgs .= ' ' . $this->getJoiner($i) . ' ';
					}
				}
				return $joinedArgs;
			}
		}
	}
?>