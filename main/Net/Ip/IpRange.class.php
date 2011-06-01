<?php
/***************************************************************************
 *   Copyright (C) 2007 by Vladimir A. Altuchov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Ip
	**/
	class IpRange implements SingleRange, DialectString, Stringable
	{
		private $startIp 	= null;
		private $endIp		= null;
		
		/**
		 * @return IpRange
		**/
		public static function create(/**/)
		{
			return new self(func_get_args());
		}
		
		public function __construct(/**/)
		{
			$args = func_get_args();
			
			if (count($args) == 1 && is_array($args[0]))
				$args = $args[0];
			
			if (count($args) == 1) { //start-end
				Assert::isString($args[0]);
				
				try {
					$parts = explode('-', $args[0]);
					
					$this->setup(
						IpAddress::create($parts[0]),
						IpAddress::create($parts[1])
					);
					
				} catch (Exception $e) {
					throw new WrongArgumentException('strange parameters received');
				}
				
			} elseif (count($args) == 2) //aka start and end
				$this->setup($args[0], $args[1]);
			
			else
				throw new WrongArgumentException('strange parameters received');
		}
		
		/**
		 * @return IpAddress
		**/
		public function getStart()
		{
			return $this->startIp;
		}
		
		/**
		 * @return IpRange
		**/
		public function setStart(IpAddress $startIp)
		{
			$this->startIp = $startIp;
			
			return $this;
		}
		
		/**
		 * @return IpAddress
		**/
		public function getEnd()
		{
			return $this->endIp;
		}
		
		/**
		 * @return IpRange
		**/
		public function setEnd(IpAddress $endIp)
		{
			$this->endIp = $endIp;
			
			return $this;
		}
		
		public function contains(/* IpAddress */ $probe)
		{
			Assert::isInstance($probe, 'IpAddress');
			
			return (
				($this->startIp->getLongIp() <= $probe->getLongIp())
				&& ($this->endIp->getLongIp() >= $probe->getLongIp())
			);
		}
		
		public function toString()
		{
			return $this->startIp->toString().'-'.$this->endIp->toString();
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteValue($this->toString());
		}
		
		private function setup(IpAddress $startIp, IpAddress $endIp)
		{
			if ($startIp->getLongIp() > $endIp->getLongIp())
				throw new WrongArgumentException(
					'start ip must be lower than ip end'
				);
			
			$this->startIp 	= $startIp;
			$this->endIp 	= $endIp;
			
			return $this;
		}
	}
?>