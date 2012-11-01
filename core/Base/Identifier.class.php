<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see Identifiable
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	namespace Onphp;

	final class Identifier implements Identifiable
	{
		private $id		= null;
		private $final	= false;
		
		/**
		 * @return \Onphp\Identifier
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\Identifier
		**/
		public static function wrap($id)
		{
			return self::create()->setId($id);
		}
		
		public function getId()
		{
			return $this->id;
		}
		
		/**
		 * @return \Onphp\Identifier
		**/
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\Identifier
		**/
		public function finalize()
		{
			$this->final = true;
			
			return $this;
		}
		
		public function isFinalized()
		{
			return $this->final;
		}
	}
?>