<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Turing
	**/
	final class CodeGenerator
	{
		private	$length				= null;
		
		private	$lowerAllowed		= true;
		private $upperAllowed		= true;
		private $numbersAllowed		= true;
		private $similarAllowed		= true;
		
		static private $similarSymbols	= array('0', 'o', '1', 'l');
		
		public function generate()
		{
			$code = null;
			
			for ($i = 0; $i < $this->length; ++$i)
				$code .= $this->generateOneSymbol();
			
			return $code;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setLength($length)
		{
			$this->length = $length;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setLowerAllowed($lowerAllowed = true)
		{
			$this->lowerAllowed = $lowerAllowed;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setUpperAllowed($upperAllowed = true)
		{
			$this->upperAllowed = $upperAllowed;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setSimilarAllowed($similarAllowed = true)
		{
			$this->similarAllowed = $similarAllowed;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setNumbersAllowed($numbersAllowed = true)
		{
			$this->numbersAllowed = $numbersAllowed;
			
			return $this;
		}
		
		/**
		 * @return CodeGenerator
		**/
		public function setCharactersAllowed($charactersAllowed = true)
		{
			$this->setLowerAllowed($charactersAllowed);
			$this->setUpperAllowed($charactersAllowed);
			
			return $this;
		}
		
		private function generateOneSymbol()
		{
			$variants = array();
			
			Assert::isTrue(
				$this->lowerAllowed
				|| $this->upperAllowed
				|| $this->numbersAllowed,
				
				'what exactly should i generate?'
			);
			
			do {
				if ($this->lowerAllowed)
					$variants[] = $this->randomChar();
				
				if ($this->upperAllowed)
					$variants[] = strtoupper($this->randomChar());
				
				if ($this->numbersAllowed)
					$variants[] = $this->randomNumber();
				
				shuffle($variants);
				
				$symbol = $variants[0];
				
			} while (
				(!$this->similarAllowed)
				&& (in_array($symbol, self::$similarSymbols))
			);
			
			return $symbol;
		}
		
		private function randomNumber()
		{
			return mt_rand(0,9);
		}
		
		private function randomChar()
		{
			return chr(mt_rand(ord('a'), ord('z')));
		}
	}
?>