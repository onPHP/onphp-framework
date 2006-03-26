<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see Identifiable
	 * 
	 * @ingroup Base
	**/
	final class Identifier implements Identifiable, Creatable
	{
		private $id		= null;
		private $final	= false;
		
		public static function create()
		{
			return new self;
		}
		
		public static function wrap($id)
		{
			return self::create()->setId($id);
		}
		
		public function getId()
		{
			return $this->id;
		}
		
		public function setId($id)
		{
			$this->id = $id;
			
			return $this;
		}
		
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