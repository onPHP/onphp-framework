<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeniy N. Sokolov	                           *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Filters
	**/
	final class WordSplitterFilter implements Filtrator
	{
		private $maxWordLength 	= 25;
		private $delimer 		= '&#x200B;';
		
		/**
		 * @return WordSplitterFilter
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return WordSplitterFilter
		**/
		public function setMaxWordLength($length)
		{
			$this->maxWordLength = $length;
			return $this;
		}
		
		public function getMaxWordLength()
		{
			return $this->maxWordLength;
		}
		
		/**
		 * @return WordSplitterFilter
		**/
		public function setDelimer($delimer)
		{
			$this->delimer = $delimer;
			return $this;
		}
		
		public function getDelimer()
		{
			return $this->delimer;
		}
		
		public function apply($value)
		{
			return
				preg_replace(
					'/([^ ]{'.$this->getMaxWordLength().','
						.$this->getMaxWordLength().'})/u',
					'$1'.$this->getDelimer(),
					$value
				);
		}
	}
?>