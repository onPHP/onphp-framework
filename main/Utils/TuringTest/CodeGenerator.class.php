<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Turing
	**/
	class CodeGenerator
	{
		private	$length				= null;
		
		private	$lowerAllowed		= true;
		private $upperAllowed		= true;
		private $numbersAllowed		= true;
	
		function generate()
		{
			mt_srand(microtime(true) * 1000000);

			$code = null;
			
			for ($i = 0; $i < $this->length; $i++)
	        	$code .= $this->generateOneSymbol();
	        	
			return $code;
		}

		public function setLength($length)
		{
			$this->length = $length;
			
			return $this;
		}
		
		public function setLowerAllowed($lowerAllowed = true)
		{
			$this->lowerAllowed = $lowerAllowed;
			
			return $this;
		}

		public function setUpperAllowed($upperAllowed = true)
		{
			$this->upperAllowed = $upperAllowed;
			
			return $this;
		}
		
		public function setNumbersAllowed($numbersAllowed = true)
		{
			$this->numbersAllowed = $numbersAllowed;
			
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
			
			if ($this->lowerAllowed)
				$variants[] = $this->randomChar();
				
			if ($this->upperAllowed)
				$variants[]= strtoupper($this->randomChar());
				
			if ($this->numbersAllowed)
				$variants[]= $this->randomNumber();
			
			shuffle($variants);
			
			return $variants[0];
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