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
/* $Id$ */

	/**
	 * @ingroup Ip
	**/
	class IpAddress implements Stringable
	{
		private $longIp = null;
		
		/**
		 * @return IpAddress
		**/
		public static function create($ip)
		{
			return new self($ip);
		}
		
		public function __construct($ip)
		{
			$this->setIp($ip);
		}
		
		/**
		 * @return IpAddress
		**/
		public function setIp($ip)
		{
			if (ip2long($ip) === -1)
				throw new WrongArgumentException('wrong ip given');
			
			$this->longIp = ip2long($ip);
			
			return $this;
		}
		
		public function getLongIp()
		{
			return $this->longIp;
		}
		
		public function toString()
		{
			return long2ip($this->longIp);
		}
	}
?>